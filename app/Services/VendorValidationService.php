<?php

namespace App\Services;

use App\Models\VendorApplication;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\SupplyCenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Exception;

class VendorValidationService
{
    protected string $validationServerUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->validationServerUrl = config('services.validation_server.url', 'http://localhost:8080');
        $this->timeout = config('services.validation_server.timeout', 30);
    }

    /**
     * Submit a new vendor application
     */
    public function submitApplication(array $data, UploadedFile $bankStatement, UploadedFile $tradingLicense): VendorApplication
    {
        try {
            // Create the application record first to get the ID
            $application = VendorApplication::create([
                'applicant_name' => $data['applicant_name'],
                'business_name' => $data['business_name'],
                'phone_number' => $data['phone_number'],
                'email' => $data['email'],
                'status' => 'pending'
            ]);

            Log::info("Created application in DB (before refresh)", [
                'id' => $application->id,
                'attributes' => $application->getAttributes()
            ]);

            // Try to refresh the model to get the actual database ID (triggers might modify it)
            try {
                $application->refresh();
                Log::info("Successfully refreshed application", [
                    'id' => $application->id,
                    'attributes' => $application->getAttributes()
                ]);
            } catch (Exception $e) {
                Log::warning("Could not refresh application, using original", [
                    'error' => $e->getMessage(),
                    'original_id' => $application->id
                ]);
                
                // Try to find by email as fallback
                $freshApplication = VendorApplication::where('email', $data['email'])
                    ->orderBy('created_at', 'desc')
                    ->first();
                    
                if ($freshApplication) {
                    $application = $freshApplication;
                    Log::info("Found application by email", [
                        'id' => $application->id,
                        'attributes' => $application->getAttributes()
                    ]);
                }
            }

            Log::info("Final application to use", [$application]);

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
            Log::error('Failed to submit vendor application', [
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
     * - POST /api/vendors/apply
     * - Form data with application details
     * - File path strings: bankStatement and tradingLicense (Java server will access files locally)
     * 
     * The Java server returns JSON with:
     * - status: string (approved, rejected, under_review, etc.)
     * - message: string (validation message)
     * - financial_data: object (optional, financial analysis results)
     * - license_data: object (optional, license verification results)
     * - references: array (optional, reference check results)
     */
    public function sendToValidationServer(VendorApplication $application): void
    {
        try {
            Log::info("Application", [$application]);
            Log::info('Sending application to validation server', [
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
            ];

            Log::info('Sending data to validation server', [
                'url' => $this->validationServerUrl . '/api/vendors/apply',
                'data' => $requestData
            ]);

            // Send HTTP request to validation server as form data (not JSON)
            $response = Http::timeout($this->timeout)
                ->asForm()  // This sends data as application/x-www-form-urlencoded instead of JSON
                ->post($this->validationServerUrl . '/api/vendors/apply', $requestData);

            Log::info('Validation server response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->processValidationResponse($application, $data);
                
                Log::info('Validation server response processed', [
                    'application_id' => $application->id,
                    'status' => $data['status'] ?? 'unknown'
                ]);
            } else {
                $errorMessage = 'Validation server returned error: ' . $response->status();
                $errorBody = $response->body();
                
                Log::error('Validation server error details', [
                    'status' => $response->status(),
                    'response_body' => $errorBody,
                    'sent_data' => $requestData
                ]);
                
                throw new Exception($errorMessage . ' - ' . $errorBody);
            }

        } catch (Exception $e) {
            Log::error('Failed to send application to validation server', [
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
            Log::info('Application marked as pending due to validation server error', [
                'application_id' => $application->id
            ]);
        }
    }

    /**
     * Process validation response from server
     */
    protected function processValidationResponse(VendorApplication $application, array $data): void
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

        Log::info('Validation response processed', [
            'application_id' => $application->id,
            'validation_status' => $status,
            'final_status' => $finalStatus,
            'message' => $message
        ]);
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
     * Approve application and create user account
     */
    public function approveApplication(VendorApplication $application): User
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
                'role' => 'supplier', // or 'vendor' based on your role system
                'phone' => $application->phone_number
            ]);

            // Automatically create supplier or wholesaler record
            $this->createRoleSpecificRecord($user);

            // Link the application to the created user
            $application->update([
                'created_user_id' => $user->id
            ]);

            Log::info('User account created for approved application', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // Note: Welcome email will be sent by the admin when they manually add the vendor to the system

            return $user;

        } catch (Exception $e) {
            Log::error('Failed to create user account for application', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject application
     */
    public function rejectApplication(VendorApplication $application, ?string $reason = null): void
    {
        // Convert empty string to null for consistent handling
        $reason = !empty($reason) ? trim($reason) : null;
        
        $application->update([
            'status' => 'rejected',
            'validation_message' => $reason ?? 'Application rejected by administrator'
        ]);

        Log::info('Application rejected', [
            'application_id' => $application->id,
            'reason' => $reason
        ]);

        // Send rejection email notification
        $this->sendRejectionEmail($application, $reason);
    }

    /**
     * Send rejection email notification
     */
    private function sendRejectionEmail(VendorApplication $application, ?string $reason = null): void
    {
        try {
            // Refresh application to get latest validation_message
            $application->refresh();
            
            // Determine the rejection reason to send in email
            // Priority: 1) Provided reason parameter 2) Updated validation_message 3) Default fallback
            $rejectionReason = null;
            
            if (!empty($reason)) {
                $rejectionReason = trim($reason);
            } elseif (!empty($application->validation_message)) {
                $rejectionReason = $application->validation_message;
            } else {
                $rejectionReason = 'Your application did not meet our requirements.';
            }
            
            // Send email to Java validation server for processing
            $emailData = [
                'type' => 'rejection',
                'email' => $application->email,
                'applicantName' => $application->applicant_name,
                'businessName' => $application->business_name,
                'applicationId' => $application->id,
                'reason' => $rejectionReason
            ];

            Log::info('Sending rejection email data to Java server', [
                'emailData' => $emailData
            ]);

            // Call Java server email endpoint using form data
            $response = Http::timeout(10)
                ->asForm()
                ->post($this->validationServerUrl . '/api/vendors/send-email', $emailData);

            Log::info('Java server response for rejection email', [
                'application_id' => $application->id,
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                Log::error('Java server returned error for rejection email', [
                    'application_id' => $application->id,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } else {
                Log::info('Rejection email sent successfully', [
                    'application_id' => $application->id,
                    'email' => $application->email
                ]);
            }

        } catch (Exception $e) {
            Log::error('Failed to send rejection email', [
                'application_id' => $application->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Get application by status token (for public status checking)
     */
    public function getApplicationByToken(string $token): ?VendorApplication
    {
        return VendorApplication::where('status_token', $token)->first();
    }

    /**
     * Retry validation for failed applications
     */
    public function retryValidation(VendorApplication $application): void
    {
        if (!$application->hasValidationFailed() && !$application->isPendingValidation()) {
            throw new Exception('Application cannot be retried for validation');
        }

        // Reset validation fields
        $application->update([
            'validated_at' => null,
            'validation_message' => null,
            'status' => 'pending'
        ]);

        // Send to validation server again
        $this->sendToValidationServer($application);
    }

    /**
     * Get applications summary for dashboard
     */
    public function getApplicationsSummary(): array
    {
        return [
            'total' => VendorApplication::count(),
            'pending' => VendorApplication::where('status', 'pending')->count(),
            'under_review' => VendorApplication::where('status', 'under_review')->count(),
            'approved' => VendorApplication::where('status', 'approved')->count(),
            'rejected' => VendorApplication::where('status', 'rejected')->count(),
            'with_user_accounts' => VendorApplication::whereNotNull('created_user_id')->count(),
            'recent' => VendorApplication::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }

    /**
     * Get applications that need attention (pending too long, failed validation, etc.)
     */
    public function getApplicationsNeedingAttention(): array
    {
        $pendingTooLong = VendorApplication::where('status', 'pending')
            ->where('created_at', '<=', now()->subDays(3))
            ->whereNull('validated_at')
            ->get();

        $validationFailed = VendorApplication::where('status', 'under_review')
            ->where('validated_at', '<=', now()->subDays(1))
            ->get();

        $approvedButNoUser = VendorApplication::where('status', 'approved')
            ->whereNull('created_user_id')
            ->get();

        return [
            'pending_too_long' => $pendingTooLong,
            'validation_failed' => $validationFailed,
            'approved_no_user' => $approvedButNoUser,
        ];
    }

    /**
     * Create supplier or wholesaler record based on user role
     */
    private function createRoleSpecificRecord(User $user)
    {
        try {
            if ($user->role === 'supplier') {
                // Get first available supply center
                $supplyCenter = SupplyCenter::first();
                if ($supplyCenter) {
                    Supplier::create([
                        'user_id' => $user->id,
                        'supply_center_id' => $supplyCenter->id,
                        'name' => $user->name,
                        'contact_person' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? '0000000000',
                        'address' => 'Address to be updated',
                        'registration_number' => 'REG' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                        'approved_date' => now()
                    ]);
                }
            } elseif ($user->role === 'vendor') {
                Wholesaler::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'contact_person' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '0000000000',
                    'address' => 'Address to be updated',
                    'distribution_region' => 'Region to be updated',
                    'registration_number' => 'WHL' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'approved_date' => now()
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't fail the user creation
            Log::error('Failed to create role-specific record', [
                'user_id' => $user->id,
                'role' => $user->role,
                'error' => $e->getMessage()
            ]);
        }
    }
}
