<?php

namespace App\Http\Controllers;

use App\Models\User; 
use App\Models\VendorApplication;
use App\Models\SupplierApplication;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\SupplyCenter;
use App\Services\VendorValidationService;
use App\Services\SupplierValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Validation\Rule;

class userManagerController extends Controller
{
    /**
     * Display a listing of the users.
     * Accessible only by admins.
     */
    public function index()
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch all users from the database
        $users = User::all();

        return view('admin.users', compact('users'));
    }

    /**
     * Store a newly created user in storage.
     * Accessible only by admins.
     */
    public function store(Request $request)
    {
        // Ensure only admins can create users
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['admin', 'supplier', 'vendor'])], // Use Rule::in for better validation
            'phone' => ['nullable', 'string', 'max:255'], // Ensure phone validation is here if you're collecting it 
        ]);

            // Get the last user's ID to generate the next sequential ID
        // This is crucial for new user creation via forms.
        /*$lastUser = User::latest('id')->first();
        $newId = 'U00001'; // Default starting ID if no users exist yet

        if ($lastUser) {
            // Extract the numeric part (e.g., '00001' from 'U00001')
            $numericPart = (int) substr($lastUser->id, 1);
            $nextNumericPart = $numericPart + 1;
            // Pad the number with leading zeros and prefix with 'U'
            $newId = 'U' . str_pad($nextNumericPart, 5, '0', STR_PAD_LEFT);
        }*/

        // Create the new user
        $user = User::create([
            /*'id' => $newId,*/
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role' => $validatedData['role'],
            'phone' => $validatedData['phone'] ?? null,
        ]);

        // Since the database trigger generates the ID, we need to retrieve it manually
        $user->id = \DB::table('users')->where('email', $user->email)->value('id');

        // Automatically create supplier or wholesaler record
        $this->createRoleSpecificRecord($user);

        // Redirect back with a success message
        return redirect()->route('admin.users.index')->with('success', 'User added successfully!');
    }

    /**
     * Update the specified user in storage.
     * Accessible only by admins.
     */
    public function update(Request $request, User $user)
    {
        // Ensure only admins can update users
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Validate the incoming request data
        $validationRules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:191', 'unique:users,email,' . $user->id],
            'role' => ['required', 'string', 'in:admin,supplier,vendor'],
        ];

        // Only validate password if it's provided (optional for updates)
        if ($request->filled('password')) {
            $validationRules['password'] = ['string', 'min:8', 'confirmed'];
        }

        $validatedData = $request->validate($validationRules);

        // Update user data
        $user->update([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'role' => $validatedData['role'],
        ]);

        // Update password only if provided
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validatedData['password'])
            ]);
        }

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     * Accessible only by admins.
     */
    public function destroy(User $user)
    {
        // Ensure only admins can delete users
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }

        // Prevent users from deleting themselves
        if ($user->id === Auth::id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        // Delete the user
        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "User '{$userName}' has been deleted successfully.");
    }

    /**
     * Get vendor applications for API (AJAX)
     */
    public function getVendorApplications(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = VendorApplication::query()->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter for vendors not yet added to system
        if ($request->has('not_added') && $request->boolean('not_added')) {
            $query->whereNull('created_user_id');
        }

        $applications = $query->get();

        return response()->json([
            'success' => true,
            'applications' => $applications
        ]);
    }

    /**
     * Get single vendor application details for API (AJAX)
     */
    public function getVendorApplicationDetails($applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $application = VendorApplication::findOrFail($applicationId);

            return response()->json([
                'success' => true,
                'application' => $application
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }
    }

    /**
     * Update vendor application status
     */
    public function updateVendorApplicationStatus(Request $request, $applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected'
        ]);

        try {
            $application = VendorApplication::findOrFail($applicationId);
            $previousStatus = $application->status;
            
            // Update application status and validation message
            $application->update([
                'status' => $validatedData['status'],
                'validation_message' => $this->getStatusUpdateMessage($validatedData['status'])
            ]);

            // Handle status-specific actions
            if ($validatedData['status'] === 'rejected') {
                // Send rejection email using the VendorValidationService
                app(VendorValidationService::class)->rejectApplication($application, $request->input('rejection_reason'));
            } elseif ($validatedData['status'] === 'approved' && $previousStatus !== 'approved') {
                // Auto-create user account when status is changed to approved
                try {
                    $this->autoCreateVendorAccount($application);
                } catch (\Exception $e) {
                    // Log the error but don't fail the status update
                    \Log::error('Failed to auto-create vendor account after approval', [
                        'application_id' => $application->id,
                        'error' => $e->getMessage()
                    ]);
                    
                    return response()->json([
                        'success' => true,
                        'message' => 'Application status updated successfully, but failed to auto-create user account. Please create manually.',
                        'warning' => 'Auto-creation failed: ' . $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => $this->getSuccessMessage($validatedData['status'], $previousStatus)
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update vendor application status', [
                'application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update application status'
            ], 500);
        }
    }

    /**
     * Get appropriate validation message for status update
     */
    private function getStatusUpdateMessage(string $status): string
    {
        return match ($status) {
            'pending' => 'Status updated to pending by administrator',
            'under_review' => 'Application is under review by administrator',
            'approved' => 'Application approved by administrator',
            'rejected' => 'Application rejected by administrator',
            default => 'Status updated by administrator'
        };
    }

    /**
     * Get appropriate success message for status update
     */
    private function getSuccessMessage(string $newStatus, string $previousStatus): string
    {
        if ($newStatus === 'approved' && $previousStatus !== 'approved') {
            return 'Application approved and vendor account created successfully';
        }
        
        return match ($newStatus) {
            'rejected' => 'Application rejected successfully. Rejection email has been sent to the applicant.',
            'approved' => 'Application approved successfully',
            'under_review' => 'Application moved to under review',
            'pending' => 'Application moved back to pending',
            default => 'Application status updated successfully'
        };
    }

    /**
     * Reject vendor application with reason
     */
    public function rejectVendorApplication(Request $request, $applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000'
        ]);

        try {
            $application = VendorApplication::findOrFail($applicationId);
            
            // Ensure rejection reason is properly handled (convert empty string to null)
            $rejectionReason = !empty($validatedData['rejection_reason']) ? $validatedData['rejection_reason'] : null;
            
            // Use the VendorValidationService to handle rejection and email sending
            app(VendorValidationService::class)->rejectApplication($application, $rejectionReason);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully. Rejection email has been sent to the applicant.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to reject vendor application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application'
            ], 500);
        }
    }

    /**
     * Add approved vendor to system with user account
     */
    public function addVendorToSystem(Request $request, $applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'default_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:default_password'
        ]);

        try {
            $application = VendorApplication::findOrFail($applicationId);

            // Check if application is approved
            if ($application->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Application must be approved before adding to system'
                ], 400);
            }

            // Check if user already exists
            if ($application->created_user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account already created for this application'
                ], 400);
            }

            // Generate next user ID
            $lastUser = User::orderBy('id', 'desc')->first();
            $nextIdNumber = $lastUser ? intval(substr($lastUser->id, 1)) + 1 : 1;
            $nextUserId = 'U' . str_pad($nextIdNumber, 5, '0', STR_PAD_LEFT);

            // Create user account
            $user = User::create([
                'id' => $nextUserId,
                'name' => $application->applicant_name,
                'email' => $application->email,
                'password' => Hash::make($validatedData['default_password']),
                'role' => 'vendor',
                'phone' => $application->phone_number
            ]);

            // Link application to user
            $application->update([
                'created_user_id' => $user->id
            ]);

            // Create wholesaler record for vendor
            $this->createRoleSpecificRecord($user);

            // Send welcome email with login credentials
            $this->sendWelcomeEmail($application, $user, $validatedData['default_password']);

            return response()->json([
                'success' => true,
                'message' => 'Vendor added to system successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add vendor to system: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send welcome email with login credentials
     */
    private function sendWelcomeEmail(VendorApplication $application, User $user, $password)
    {
        try {
            // Send email to Java validation server for processing
            $emailData = [
                'type' => 'welcome',
                'email' => $user->email,
                'applicantName' => $user->name,
                'businessName' => $application->business_name,
                'userId' => $user->id,
                'password' => $password,
                'loginUrl' => route('login')
            ];

            \Log::info('Sending welcome email data to Java server', $emailData);

            // Call Java server email endpoint using form data
            $response = Http::timeout(10)
                ->asForm()
                ->post(config('services.validation_server.url', 'http://localhost:8081') . '/api/vendors/send-email', $emailData);

            \Log::info('Java server response for welcome email', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                \Log::error('Java server returned error for welcome email', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } else {
                \Log::info('Welcome email sent successfully', [
                    'email' => $user->email,
                    'userId' => $user->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to send welcome email', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Send supplier welcome email with login credentials
     */
    private function sendSupplierWelcomeEmail(SupplierApplication $application, User $user, $password, $roleData = null)
    {
        try {
            // Prepare basic email data
            $emailData = [
                'type' => 'welcome',
                'email' => $user->email,
                'applicantName' => $user->name,
                'businessName' => $application->business_name,
                'userId' => $user->id,
                'password' => $password,
                'loginUrl' => route('login')
            ];

            // Add supply center information if available
            if ($roleData && isset($roleData['supplyCenter'])) {
                $supplyCenter = $roleData['supplyCenter'];
                $emailData['supplyCenterName'] = $supplyCenter->name;
                $emailData['supplyCenterLocation'] = $supplyCenter->location;
            }

            Log::info('Sending supplier welcome email data to Java server', $emailData);

            // Call Java server email endpoint using form data  
            $response = Http::timeout(10)
                ->asForm()
                ->post(config('services.validation_server.url', 'http://localhost:8081') . '/api/suppliers/send-email', $emailData);

            Log::info('Java server response for supplier welcome email', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->failed()) {
                Log::error('Java server returned error for supplier welcome email', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } else {
                Log::info('Supplier welcome email sent successfully', [
                    'email' => $user->email,
                    'userId' => $user->id
                ]);
            }

        } catch (Exception $e) {
            Log::error('Failed to send supplier welcome email', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Create supplier or wholesaler record based on user role
     */
    private function createRoleSpecificRecord(User $user)
    {
        // Ensure user has an ID
        if (!$user->id) {
            \Log::error('Cannot create role-specific record: User has no ID', [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'role' => $user->role
            ]);
            return null;
        }

        try {
            \Log::info('Creating role-specific record', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => $user->role
            ]);
            
            if ($user->role === 'supplier') {
                // Get first available supply center for supplier assignment
                $supplyCenter = SupplyCenter::first();
                if ($supplyCenter) {
                    \Log::info('Creating supplier record', [
                        'user_id' => $user->id,
                        'supply_center_id' => $supplyCenter->id
                    ]);
                    
                    $supplier = Supplier::create([
                        'user_id' => $user->id,
                        'supply_center_id' => $supplyCenter->id,
                        'name' => $user->name,
                        'contact_person' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone ?? '0000000000',
                        'address' => 'Address to be updated',
                        'registration_number' => 'REG' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . time(),
                        'approved_date' => now()
                    ]);
                    
                    \Log::info('Supplier record created successfully', [
                        'user_id' => $user->id,
                        'supplier_id' => $supplier->id,
                        'supply_center_id' => $supplyCenter->id
                    ]);
                    
                    return ['supplyCenter' => $supplyCenter];
                } else {
                    \Log::error('No supply center found for supplier creation', [
                        'user_id' => $user->id
                    ]);
                }
            } elseif ($user->role === 'vendor') {
                \Log::info('Creating wholesaler record', [
                    'user_id' => $user->id
                ]);
                
                $wholesaler = Wholesaler::create([
                    'user_id' => $user->id,
                    'name' => $user->name,
                    'contact_person' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '0000000000',
                    'address' => 'Address to be updated',
                    'distribution_region' => 'Region to be updated',
                    'registration_number' => 'WHL' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT) . time(),
                    'approved_date' => now()
                ]);
                
                \Log::info('Wholesaler record created successfully', [
                    'user_id' => $user->id,
                    'wholesaler_id' => $wholesaler->id
                ]);
                
                return null; // No specific data to return for vendors
            }
        } catch (\Exception $e) {
            // Log detailed error information
            \Log::error('Failed to create role-specific record', [
                'user_id' => $user->id,
                'role' => $user->role,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't fail the user creation, but we could optionally flash an error message
            // session()->flash('warning', 'User created but there was an issue creating the associated record. Please contact support.');
        }
        
        return null;
    }

    /**
     * Automatically create vendor account when application is approved
     */
    private function autoCreateVendorAccount(VendorApplication $application)
    {
        // Check if user already exists
        if ($application->created_user_id) {
            \Log::info('User account already exists for application', [
                'application_id' => $application->id,
                'user_id' => $application->created_user_id
            ]);
            return; // Already created, skip
        }

        // Generate a secure random password
        $temporaryPassword = $this->generateSecurePassword();

        // Generate next user ID
        $lastUser = User::orderBy('id', 'desc')->first();
        $nextIdNumber = $lastUser ? intval(substr($lastUser->id, 1)) + 1 : 1;
        $nextUserId = 'U' . str_pad($nextIdNumber, 5, '0', STR_PAD_LEFT);

        \Log::info('Auto-creating vendor account', [
            'application_id' => $application->id,
            'next_user_id' => $nextUserId,
            'email' => $application->email
        ]);

        // Create user account
        $user = User::create([
            'id' => $nextUserId,
            'name' => $application->applicant_name,
            'email' => $application->email,
            'password' => Hash::make($temporaryPassword),
            'role' => 'vendor',
            'phone' => $application->phone_number
        ]);

        // Link application to user
        $application->update([
            'created_user_id' => $user->id
        ]);

        // Create wholesaler record for vendor
        $this->createRoleSpecificRecord($user);

        // Send welcome email with login credentials
        $this->sendWelcomeEmail($application, $user, $temporaryPassword);

        \Log::info('Vendor account auto-created successfully', [
            'application_id' => $application->id,
            'user_id' => $user->id,
            'email' => $user->email
        ]);
    }

    /**
     * Generate a secure random password
     */
    private function generateSecurePassword($length = 12)
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%&*';
        
        // Ensure at least one character from each type
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        // Fill the rest with random characters
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        // Shuffle the password
        return str_shuffle($password);
    }

    /**
     * Get supplier applications for API (AJAX)
     */
    public function getSupplierApplications(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = SupplierApplication::query()->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter for suppliers not yet added to system
        if ($request->has('not_added') && $request->boolean('not_added')) {
            $query->whereNull('created_user_id');
        }

        $applications = $query->get();

        return response()->json([
            'success' => true,
            'applications' => $applications
        ]);
    }

    /**
     * Get single supplier application details for API (AJAX)
     */
    public function getSupplierApplicationDetails($applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $application = SupplierApplication::findOrFail($applicationId);
            
            return response()->json([
                'success' => true,
                'application' => $application
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }
    }

    /**
     * Update supplier application status
     */
    public function updateSupplierApplicationStatus(Request $request, $applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,under_review,approved,rejected',
            'rejection_reason' => 'required_if:status,rejected|string|max:500'
        ]);

        try {
            $application = SupplierApplication::findOrFail($applicationId);
            
            $oldStatus = $application->status;
            $newStatus = $request->status;
            
            $application->status = $newStatus;
            
            if ($newStatus === 'rejected') {
                $application->rejection_reason = $request->rejection_reason;
                $application->rejected_at = now();
                $application->rejected_by = Auth::id();
            } else if ($newStatus === 'approved') {
                $application->approved_at = now();
                $application->approved_by = Auth::id();
                $application->rejection_reason = null;
                
                // Automatically add supplier to system when approved
                if (!$application->created_user_id) {
                    $this->autoAddSupplierToSystem($application);
                }
                
            } else if ($newStatus === 'under_review') {
                $application->under_review_at = now();
                $application->under_review_by = Auth::id();
                $application->rejection_reason = null;
            }
            
            $application->save();

            return response()->json([
                'success' => true,
                'message' => $newStatus === 'approved' 
                    ? 'Application approved and supplier added to system successfully'
                    : 'Application status updated successfully',
                'application' => $application
            ]);
            
        } catch (Exception $e) {
            Log::error('Error updating supplier application status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update application status'
            ], 500);
        }
    }

    /**
     * Reject supplier application with reason
     */
    public function rejectSupplierApplication(Request $request, $applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000'
        ]);

        try {
            $application = SupplierApplication::findOrFail($applicationId);
            
            // Ensure rejection reason is properly handled (convert empty string to null)
            $rejectionReason = !empty($validatedData['rejection_reason']) ? $validatedData['rejection_reason'] : null;
            
            // Use the SupplierValidationService to handle rejection and email sending
            app(SupplierValidationService::class)->rejectApplication($application, $rejectionReason);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully. Rejection email has been sent to the applicant.'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reject supplier application', [
                'application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application'
            ], 500);
        }
    }

    /**
     * Add approved supplier to system as user
     */
    public function addSupplierToSystem(Request $request, $applicationId)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'default_password' => 'required|string|min:8',
            'confirm_password' => 'required|string|same:default_password'
        ]);

        try {
            $application = SupplierApplication::findOrFail($applicationId);

            // Check if application is approved
            if ($application->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Application must be approved before adding to system'
                ], 400);
            }

            // Check if user already exists
            if ($application->created_user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User account already created for this application'
                ], 400);
            }

            // Generate next user ID
            $lastUser = User::orderBy('id', 'desc')->first();
            $nextIdNumber = $lastUser ? intval(substr($lastUser->id, 1)) + 1 : 1;
            $nextUserId = 'U' . str_pad($nextIdNumber, 5, '0', STR_PAD_LEFT);

            // Create user account
            $user = User::create([
                'id' => $nextUserId,
                'name' => $application->applicant_name,
                'email' => $application->email,
                'password' => Hash::make($validatedData['default_password']),
                'role' => 'supplier',
                'phone' => $application->phone_number
            ]);

            // Link application to user
            $application->update([
                'created_user_id' => $user->id
            ]);

            // Create supplier record for user
            $roleData = $this->createRoleSpecificRecord($user);

            // Send welcome email with login credentials
            $this->sendSupplierWelcomeEmail($application, $user, $validatedData['default_password'], $roleData);

            return response()->json([
                'success' => true,
                'message' => 'Supplier added to system successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add supplier to system: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automatically add supplier to system when approved (internal method)
     */
    private function autoAddSupplierToSystem(SupplierApplication $application)
    {
        try {
            // Check if user with this email already exists
            $existingUser = User::where('email', $application->email)->first();
            if ($existingUser) {
                Log::warning('User with email already exists, skipping auto-creation', [
                    'email' => $application->email,
                    'existing_user_id' => $existingUser->id
                ]);
                return;
            }

            // Generate next user ID
            $lastUser = User::orderBy('id', 'desc')->first();
            $nextIdNumber = $lastUser ? intval(substr($lastUser->id, 1)) + 1 : 1;
            $nextUserId = 'U' . str_pad($nextIdNumber, 5, '0', STR_PAD_LEFT);

            // Generate a random password
            $password = $this->generateRandomPassword();

            // Create user account
            $user = User::create([
                'id' => $nextUserId,
                'name' => $application->applicant_name,
                'email' => $application->email,
                'password' => Hash::make($password),
                'role' => 'supplier',
                'phone' => $application->phone_number
            ]);

            // Link application to user
            $application->update([
                'created_user_id' => $user->id
            ]);

            // Create supplier record for user
            $roleData = $this->createRoleSpecificRecord($user);

            // Send welcome email with login credentials
            $this->sendSupplierWelcomeEmail($application, $user, $password, $roleData);

            Log::info('Supplier automatically added to system on approval', [
                'application_id' => $application->id,
                'user_id' => $user->id,
                'email' => $user->email
            ]);

        } catch (Exception $e) {
            Log::error('Failed to automatically add supplier to system', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);
            // Don't throw the exception - we don't want approval to fail if user creation fails
        }
    }

    /**
     * Get admin emails for notifications
     * Returns a list of all admin user emails
     */
    public function getAdminEmails()
    {
        try {
            // Get all users with admin role
            $adminEmails = User::where('role', 'admin')
                             ->whereNotNull('email')
                             ->pluck('email')
                             ->toArray();

            return response()->json([
                'success' => true,
                'data' => $adminEmails,
                'count' => count($adminEmails)
            ]);

        } catch (Exception $e) {
            Log::error('Failed to fetch admin emails', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admin emails',
                'data' => []
            ], 500);
        }
    }
}


