<?php

namespace App\Services;

use App\Models\SupplierApplication;
use App\Models\User;
use App\Models\Supplier;
use App\Models\SupplyCenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class SupplierValidationService
{
    protected string $validationServerUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->validationServerUrl = config('services.validation_server.url', 'http://localhost:8080');
        $this->timeout = config('services.validation_server.timeout', 30);
    }

    /**
     * Submit a new supplier application
     */
    public function submitApplication(array $data, UploadedFile $bankStatement, UploadedFile $tradingLicense): SupplierApplication
    {
        try {
            // Create the application record first to get the ID
            $application = SupplierApplication::create([
                'applicant_name' => $data['applicant_name'],
                'business_name' => $data['business_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'status' => 'pending'
            ]);

            Log::info("Created supplier application in DB (before refresh)", [
                'id' => $application->id,
                'attributes' => $application->getAttributes()
            ]);

            // Try to refresh the model to get the actual database ID (triggers might modify it)
            try {
                $application->refresh();
                Log::info("Successfully refreshed supplier application", [
                    'id' => $application->id,
                    'attributes' => $application->getAttributes()
                ]);
            } catch (Exception $e) {
                Log::warning("Could not refresh supplier application, using original", [
                    'error' => $e->getMessage(),
                    'original_id' => $application->id
                ]);
                
                // Try to find by email as fallback
                $freshApplication = SupplierApplication::where('email', $data['email'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($freshApplication) {
                    $application = $freshApplication;
                    Log::info("Found supplier application by email", [
                        'id' => $application->id,
                        'attributes' => $application->getAttributes()
                    ]);
                }
            }

            Log::info("Final supplier application to use", [$application]);

            // Store the files with proper naming
            $bankStatementPath = $application->storeBankStatement($bankStatement);
            $tradingLicensePath = $application->storeTradingLicense($tradingLicense);

            // Update the application with file paths
            $application->update([
                'bank_statement_path' => $bankStatementPath,
                'trading_license_path' => $tradingLicensePath
            ]);

            // Send to validation server
            $this->sendToValidationServer($application);

            return $application;

        } catch (Exception $e) {
            Log::error('Failed to submit supplier application', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Send application to validation server
     * 
     * The Java validation server expects:
     * - POST /api/suppliers/apply
     * - Form data with application details
     * - File path strings: bankStatement and tradingLicense (Java server will access files locally)
     * 
     * The Java server returns JSON with:
     * - status: string (approved, rejected, under_review, etc.)
     * - message: string (validation message)
     * - financial_data: object (optional, financial analysis results - lighter than vendor)
     * - license_data: object (optional, license verification results)
     * - references: array (optional, reference check results)
     */
    public function sendToValidationServer(SupplierApplication $application): void
    {
        try {
            Log::info("Supplier Application", [$application]);
            Log::info('Sending supplier application to validation server', [
                'application_id' => $application->id,
                'server_url' => $this->validationServerUrl
            ]);

            // Prepare the data to send - use absolute paths for Java server  
            // Send the actual database ID so Java server can fetch the record
            $storagePath = storage_path('app/private/');
            $requestData = [
                'applicantId' => $application->id,  // Java server needs this to fetch the record
                'name' => $application->applicant_name,
                'businessName' => $application->business_name,
                'email' => $application->email,
                'phoneNumber' => $application->phone_number,
                'bankStatement' => $storagePath . $application->bank_statement_path,
                'tradingLicense' => $storagePath . $application->trading_license_path,
                'applicationType' => 'supplier' // Indicate this is a supplier application
            ];

            Log::info('Sending supplier data to validation server', [
                'url' => $this->validationServerUrl . '/api/suppliers/apply',
                'data' => $requestData
            ]);

            // Send HTTP request to validation server as form data (not JSON)
            $response = Http::timeout($this->timeout)
                ->asForm()  // This sends data as application/x-www-form-urlencoded instead of JSON
                ->post($this->validationServerUrl . '/api/suppliers/apply', $requestData);

            Log::info('Supplier validation server response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->processValidationResponse($application, $data);
                
                Log::info('Supplier validation server response processed', [
                    'application_id' => $application->id,
                    'status' => $data['status'] ?? 'unknown'
                ]);
            } else {
                $errorMessage = 'Validation server returned error: ' . $response->status();
                $errorBody = $response->body();
                
                Log::error('Supplier validation server error details', [
                    'status' => $response->status(),
                    'response_body' => $errorBody,
                    'sent_data' => $requestData
                ]);
                
                throw new Exception($errorMessage . ' - ' . $errorBody);
            }

        } catch (Exception $e) {
            Log::error('Failed to send supplier application to validation server', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            // Update application with error status - keep as pending for manual review
            $application->update([
                'validation_message' => 'Failed to communicate with validation server: ' . $e->getMessage(),
                'validated_at' => now(),
                'status' => 'pending' // Keep as pending instead of under_review for validation failures
            ]);

            // Don't throw exception to allow application to be submitted
            // Admin can retry validation manually
            Log::info('Supplier application marked as pending due to validation server error', [
                'application_id' => $application->id
            ]);
        }
    }

    /**
     * Process validation response from server
     */
    protected function processValidationResponse(SupplierApplication $application, array $data): void
    {
        $status = $data['status'] ?? 'error';
        $message = $data['message'] ?? 'No message provided';

        // Determine final status based on validation server response
        $finalStatus = $this->determineFinalStatus($status, $data);

        // Store the response from the Java validation server
        $application->update([
            'validation_message' => $message,
            'validated_at' => now(),
            'status' => $finalStatus,
            // Store any additional data returned by the Java validation server
            'financial_data' => $data['financial_data'] ?? null,
            'license_data' => $data['license_data'] ?? null,
            'references' => $data['references'] ?? null,
            // Store visit date if provided and validation was successful
            'visit_scheduled' => ($finalStatus === 'under_review' && isset($data['visit_date'])) 
                ? $data['visit_date'] : null
        ]);

        Log::info('Supplier validation response processed', [
            'application_id' => $application->id,
            'validation_status' => $status,
            'final_status' => $finalStatus,
            'message' => $message
        ]);

        // If validation was successful, send email notification
        if ($finalStatus === 'under_review' && isset($data['visit_date'])) {
            $this->sendVisitScheduledEmail($application, $data['visit_date']);
        }
    }

    /**
     * Determine final application status based on validation results
     */
    private function determineFinalStatus(string $validationStatus, array $data): string
    {
        switch ($validationStatus) {
            case 'approved':
                return 'approved';
            
            case 'rejected':
                return 'rejected';
            
            case 'verified':
            case 'validation_successful':
            case 'submitted': // Map Java server "submitted" to "under_review"
                // Only set to under_review if validation was successful and visit is scheduled
                return isset($data['visit_date']) ? 'under_review' : 'pending';
            
            case 'validation_failed':
            case 'error':
            case 'failed':
            default:
                // Keep as pending for failed validations - requires manual admin review
                return 'pending';
        }
    }

    /**
     * Send email notification about scheduled visit
     */
    private function sendVisitScheduledEmail(SupplierApplication $application, string $visitDate): void
    {
        try {
            Log::info('Sending visit scheduled email to supplier', [
                'application_id' => $application->id,
                'email' => $application->email,
                'visit_date' => $visitDate
            ]);

            // TODO: Implement email sending
            // Mail::to($application->email)->send(new SupplierVisitScheduled($application, $visitDate));
            
            Log::info('Visit scheduled email sent to supplier', [
                'application_id' => $application->id,
                'email' => $application->email
            ]);

        } catch (Exception $e) {
            Log::error('Failed to send visit scheduled email to supplier', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get application by status token
     */
    public function getApplicationByToken(string $token): ?SupplierApplication
    {
        return SupplierApplication::where('status_token', $token)->first();
    }

    /**
     * Approve application and create user account
     */
    public function approveApplication(SupplierApplication $application): User
    {
        if (!$application->isApproved()) {
            throw new Exception('Application must be in approved status');
        }

        if ($application->created_user_id) {
            throw new Exception('User account already created for this application');
        }

        try {
            // Generate temporary password
            $temporaryPassword = Str::random(12);

            // Create user account
            $user = User::create([
                'name' => $application->applicant_name,
                'email' => $application->email,
                'password' => Hash::make($temporaryPassword),
                'role' => 'supplier',
                'phone' => $application->phone_number
            ]);

            // Create supplier record
            $this->createSupplierRecord($user, $application);

            // Link the application to the created user
            $application->update([
                'created_user_id' => $user->id
            ]);

            Log::info('Supplier user account created for approved application', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // TODO: Send welcome email with temporary password
            // Mail::to($user->email)->send(new SupplierWelcome($user, $temporaryPassword));

            return $user;

        } catch (Exception $e) {
            Log::error('Failed to approve supplier application', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Create supplier record for approved application
     */
    private function createSupplierRecord(User $user, SupplierApplication $application): void
    {
        try {
            // Find or create a default supply center
            $supplyCenter = SupplyCenter::first();
            if (!$supplyCenter) {
                $supplyCenter = SupplyCenter::create([
                    'name' => 'Default Supply Center',
                    'location' => 'Default Location'
                ]);
            }

            // Create supplier record
            $supplier = Supplier::create([
                'user_id' => $user->id,
                'supply_center_id' => $supplyCenter->id,
                'business_name' => $application->business_name,
                'phone' => $application->phone_number,
                'email' => $application->email,
                'status' => 'active'
            ]);

            Log::info('Supplier record created', [
                'supplier_id' => $supplier->id,
                'user_id' => $user->id,
                'supply_center_id' => $supplyCenter->id
            ]);

        } catch (Exception $e) {
            Log::error('Failed to create supplier record', [
                'user_id' => $user->id,
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject application and send notification
     */
    public function rejectApplication(SupplierApplication $application, ?string $reason = null): void
    {
        try {
            $application->update([
                'status' => 'rejected',
                'validation_message' => $reason ?? 'Application rejected by administrator',
                'validated_at' => now()
            ]);

            Log::info('Supplier application rejected', [
                'application_id' => $application->id,
                'reason' => $reason
            ]);

            // TODO: Send rejection email
            // Mail::to($application->email)->send(new SupplierApplicationRejected($application, $reason));

        } catch (Exception $e) {
            Log::error('Failed to reject supplier application', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Retry validation for failed applications
     */
    public function retryValidation(SupplierApplication $application): void
    {
        if (!in_array($application->status, ['pending', 'rejected'])) {
            throw new Exception('Application must be in pending or rejected status to retry validation');
        }

        if (!$application->hasAllDocuments()) {
            throw new Exception('Application must have all required documents to retry validation');
        }

        // Reset status and retry validation
        $application->update([
            'status' => 'pending',
            'validation_message' => null,
            'validated_at' => null
        ]);

        $this->sendToValidationServer($application);
    }
}
