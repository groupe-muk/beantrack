<?php

namespace App\Http\Controllers;

use App\Models\VendorApplication;
use App\Services\VendorValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Storage;

class VendorApplicationController extends Controller
{
    protected VendorValidationService $validationService;

    public function __construct(VendorValidationService $validationService)
    {
        $this->validationService = $validationService;
    }

    /**
     * Show the vendor onboarding page
     */
    public function vendorOnboarding()
    {
        return view('vendor.onboarding');
    }

    /**
     * Display the application form
     */
    public function create()
    {
        return view('vendor.apply');
    }

    /**
     * Store a new vendor application
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Authorization check - anyone can apply (public endpoint)
            $this->authorize('create', VendorApplication::class);
            
            // Inner try-catch for main logic
            // Validate the request
            $validator = Validator::make($request->all(), [
                'applicant_name' => 'required|string|max:255',
                'business_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:20',
                'email' => 'required|email|unique:vendor_applications,email|unique:users,email',
                'bank_statement' => 'required|file|mimes:pdf|max:10240', // 10MB max
                'trading_license' => 'required|file|mimes:pdf|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Submit the application
            $application = $this->validationService->submitApplication(
                $request->only(['applicant_name', 'business_name', 'phone_number', 'email']),
                $request->file('bank_statement'),
                $request->file('trading_license')
            );

            Log::info('Vendor application submitted successfully', [
                'application_id' => $application->id,
                'email' => $application->email
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Application submitted successfully',
                'data' => [
                    'application_id' => $application->id,
                    'status_token' => $application->status_token,
                    'status' => $application->status
                ]
            ], 201);

        } catch (Exception $e) {
            Log::error('Failed to submit vendor application', [
                'error' => $e->getMessage(),
                'request_data' => $request->except(['bank_statement', 'trading_license'])
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
            
        } catch (Exception $outerException) {
            // Final safety net for any unexpected errors
            Log::error('Unexpected error in vendor application submission', [
                'error' => $outerException->getMessage(),
                'trace' => $outerException->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
                'error' => config('app.debug') ? $outerException->getMessage() : null
            ], 500);
        }
    }

    /**
     * Show the status check form
     */
    public function checkStatus()
    {
        return view('vendor.status');
    }

    /**
     * Show application status (public endpoint)
     */
    public function status(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|size:32',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token format'
            ], 422);
        }

        $application = $this->validationService->getApplicationByToken($request->token);

        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'Application not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'application_id' => $application->id,
                'applicant_name' => $application->applicant_name,
                'business_name' => $application->business_name,
                'status' => $application->status,
                'validation_message' => $application->validation_message,
                'submitted_at' => $application->created_at,
                'validated_at' => $application->validated_at,
                'visit_scheduled' => $application->visit_scheduled
            ]
        ]);
    }

    /**
     * List all applications (Admin only)
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', VendorApplication::class);

        $query = VendorApplication::query()->latest();

        // Filter by status if provided
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('applicant_name', 'like', "%{$search}%")
                  ->orWhere('business_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applications = $query->paginate(15);

        return view('admin.vendor-applications.index', compact('applications'));
    }

    /**
     * Show detailed application view (Admin only)
     */
    public function show(VendorApplication $application)
    {
        try {
            $this->authorize('view', $application);

            // Handle AJAX requests with JSON response
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'application' => $application->load(['createdUser'])
                ]);
            }

            // Handle regular web requests with view
            return view('admin.vendor-applications.show', compact('application'));
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }
            abort(403, 'Unauthorized access');
        } catch (\Exception $e) {
            \Log::error('Error in VendorApplicationController@show: ' . $e->getMessage());
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading application details: ' . $e->getMessage()
                ], 500);
            }
            abort(500, 'Error loading application');
        }
    }

    /**
     * Approve application (Admin only)
     */
    public function approve(VendorApplication $application): JsonResponse
    {
        $this->authorize('update', $application);

        try {
            if (!$application->isApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application must be in approved status from validation server'
                ], 400);
            }

            $user = $this->validationService->approveApplication($application);

            return response()->json([
                'success' => true,
                'message' => 'Application approved and user account created',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to approve application', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve application: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject application (Admin only)
     */
    public function reject(Request $request, VendorApplication $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use the validation service to handle rejection and email sending
            $this->validationService->rejectApplication($application, $request->rejection_reason);

            return response()->json([
                'success' => true,
                'message' => 'Application rejected successfully. Rejection email has been sent to the applicant.'
            ]);

        } catch (Exception $e) {
            Log::error('Failed to reject application', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject application'
            ], 500);
        }
    }

    /**
     * Download application document (Admin only)
     */
    public function downloadDocument(VendorApplication $application, string $type)
    {
        $this->authorize('view', $application);

        $filePath = match ($type) {
            'bank-statement' => $application->bank_statement_path,
            'trading-license' => $application->trading_license_path,
            default => null
        };

        if (!$filePath || !Storage::disk('local')->exists($filePath)) {
            abort(404, 'Document not found');
        }

        $fileName = match ($type) {
            'bank-statement' => $application->business_name . '_bank_statement.pdf',
            'trading-license' => $application->business_name . '_trading_license.pdf',
            default => 'document.pdf'
        };

        return Storage::disk('local')->download($filePath, $fileName);
    }

    /**
     * Retry validation for failed applications (Admin only)
     */
    public function retryValidation(VendorApplication $application): JsonResponse
    {
        $this->authorize('update', $application);

        try {
            $this->validationService->retryValidation($application);

            return response()->json([
                'success' => true,
                'message' => 'Validation retry initiated'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Schedule a visit for the application (Admin only)
     */
    public function scheduleVisit(Request $request, VendorApplication $application): JsonResponse
    {
        $this->authorize('update', $application);

        $validator = Validator::make($request->all(), [
            'visit_date' => 'required|date|after:today'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $application->update([
                'visit_scheduled' => $request->visit_date
            ]);

            Log::info('Visit scheduled for vendor application', [
                'application_id' => $application->id,
                'visit_date' => $request->visit_date
            ]);

            // TODO: Send email notification about scheduled visit
            // Mail::to($application->email)->send(new VisitScheduled($application));

            return response()->json([
                'success' => true,
                'message' => 'Visit scheduled successfully',
                'data' => [
                    'visit_date' => $application->visit_scheduled
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Failed to schedule visit', [
                'application_id' => $application->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule visit'
            ], 500);
        }
    }
}
