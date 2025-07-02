<?php

namespace App\Services;

use App\Models\VendorApplication;
use App\Models\User;
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

            // Update application with error status
            $application->update([
                'validation_message' => 'Failed to communicate with validation server: ' . $e->getMessage(),
                'validated_at' => now(),
                'status' => 'under_review' // Will require manual admin review
            ]);

            throw $e;
        }
    }

    /**
     * Process validation response from server
     */
    protected function processValidationResponse(VendorApplication $application, array $data): void
    {
        $status = $data['status'] ?? 'error';
        $message = $data['message'] ?? 'No message provided';

        // Simply store the response from the Java validation server as-is
        // The Java server determines the validation logic and status
        $application->update([
            'validation_message' => $message,
            'validated_at' => now(),
            'status' => $status, // Use status directly from Java server
            // Store any additional data returned by the Java validation server
            'financial_data' => $data['financial_data'] ?? null,
            'license_data' => $data['license_data'] ?? null,
            'references' => $data['references'] ?? null
        ]);
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

            // Link the application to the created user
            $application->update([
                'created_user_id' => $user->id
            ]);

            Log::info('User account created for approved application', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            // TODO: Send email with login credentials
            // Mail::to($user->email)->send(new VendorAccountCreated($user, $temporaryPassword));

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
        $application->update([
            'status' => 'rejected',
            'validation_message' => $reason ?? $application->validation_message ?? 'Application rejected by administrator'
        ]);

        Log::info('Application rejected', [
            'application_id' => $application->id,
            'reason' => $reason
        ]);

        // TODO: Send rejection email
        // Mail::to($application->email)->send(new VendorApplicationRejected($application, $reason));
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
        if (!$application->isPendingValidation()) {
            throw new Exception('Application is not pending validation');
        }

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
}
