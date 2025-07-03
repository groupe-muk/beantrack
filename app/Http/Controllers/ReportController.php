<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReportController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $currentUser = Auth::user();
        
        // Admin dashboard should show admin reports including their own
        $adminReportsQuery = Report::where(function($q) use ($currentUser) {
            $q->where('created_by', $currentUser->id) // Reports created by current admin
              ->orWhere(function($subQ) {
                  $subQ->whereHas('creator', function($userQuery) {
                      $userQuery->where('role', '!=', 'supplier');
                  });
              })
              ->orWhereNull('created_by'); // Legacy reports without creator
        });

        $activeReports = $adminReportsQuery->where('status', 'active')->count();
        
        // For "Generated Today", check both last_sent and completed reports created today
        $generatedTodayBySent = (clone $adminReportsQuery)->whereDate('last_sent', today())->count();
        $generatedTodayByCreated = (clone $adminReportsQuery)->whereDate('created_at', today())
                                              ->whereIn('status', ['completed', 'delivered'])
                                              ->count();
        $generatedToday = max($generatedTodayBySent, $generatedTodayByCreated);
        
        $pendingReports = $adminReportsQuery->where('status', 'pending')->count();
        
        // Calculate success rate (example calculation) - only for admin reports
        $totalReports = $adminReportsQuery->whereDate('created_at', '>=', now()->subDays(30))->count();
        $successfulReports = $adminReportsQuery->whereDate('created_at', '>=', now()->subDays(30))
                                             ->where('status', '!=', 'failed')->count();
        $successRate = $totalReports > 0 ? round(($successfulReports / $totalReports) * 100, 1) : 0;

        return view('reports.report', compact(
            'activeReports', 
            'generatedToday', 
            'pendingReports', 
            'successRate'
        ));
    }

    /**
     * Display the supplier reports dashboard
     */
    public function supplierIndex()
    {
        // Get supplier-specific stats
        $activeReports = Report::where('created_by', Auth::id())
                              ->where('status', 'active')
                              ->count();
        
        $generatedToday = Report::where('created_by', Auth::id())
                               ->whereDate('last_sent', today())
                               ->count();
        
        $totalReports = Report::where('created_by', Auth::id())->count();
        
        $lastReport = Report::where('created_by', Auth::id())
                           ->whereNotNull('last_sent')
                           ->orderBy('last_sent', 'desc')
                           ->first();
        
        $lastGenerated = $lastReport ? $lastReport->last_sent->format('M d') : 'Never';

        return view('reports.supplier-report', compact(
            'activeReports', 
            'generatedToday', 
            'totalReports', 
            'lastGenerated'
        ));
    }

    /**
     * Get report library data for DataTables
     */
    public function getReportLibrary(Request $request)
    {
        $currentUser = Auth::user();
        $query = Report::with('recipient');

        // Always filter by user role and ownership
        if ($currentUser->role === 'supplier') {
            // Suppliers can only see their own reports
            $query->where('created_by', $currentUser->id);
        } else {
            // Admins should NOT see supplier reports by default
            // Only show reports created by admins (or reports without a created_by field for legacy)
            $query->where(function($q) {
                $q->whereHas('creator', function($userQuery) {
                    $userQuery->where('role', '!=', 'supplier');
                })->orWhereNull('created_by'); // Include legacy reports without creator
            });
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply type filter
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('format', $request->type);
        }

        // Apply frequency filter
        if ($request->has('frequency') && $request->frequency !== 'all') {
            $query->where('frequency', $request->frequency);
        }

        $reports = $query->get();

        return response()->json([
            'data' => $reports->map(function($report) {
                return [
                    'id' => $report->id,
                    'name' => $report->name ?? 'Untitled Report',
                    'description' => $report->description ?? 'No description available',
                    'type' => $report->format ?? 'PDF',
                    'format' => $report->format ?? 'pdf', // Add format field for frontend
                    'frequency' => ucfirst($report->frequency),
                    'recipients' => $report->recipients ?? 'Not specified',
                    'last_sent' => $report->last_sent, // Return the actual datetime object
                    'last_generated' => $report->last_sent ? $report->last_sent->format('Y-m-d') : 'Never',
                    'status' => $report->status ?? 'active',
                    'file_size' => $report->file_size ?? $this->calculateReportSize($report), // Use stored size or calculate
                    'updated_at' => $report->updated_at, // Also include updated_at for fallback
                    'actions' => $this->generateActionButtons($report->id)
                ];
            })
        ]);
    }

    /**
     * Get historical reports data
     */
    public function getHistoricalReports(Request $request)
    {
        $currentUser = Auth::user();
        $query = Report::with('recipient')->whereNotNull('last_sent');

        // Always filter by user role and ownership
        if ($currentUser->role === 'supplier') {
            // Suppliers can only see their own reports
            $query->where('created_by', $currentUser->id);
        } else {
            // Admins should NOT see supplier reports by default
            // Only show reports created by admins (or reports without a created_by field for legacy)
            $query->where(function($q) {
                $q->whereHas('creator', function($userQuery) {
                    $userQuery->where('role', '!=', 'supplier');
                })->orWhereNull('created_by'); // Include legacy reports without creator
            });
        }

        // Apply search filter
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhereHas('recipient', function($userQuery) use ($searchTerm) {
                      $userQuery->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Apply date range filter
        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->whereDate('last_sent', '>=', $request->from_date);
        }
        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->whereDate('last_sent', '<=', $request->to_date);
        }

        $reports = $query->orderBy('last_sent', 'desc')->get();

        return response()->json([
            'data' => $reports->map(function($report) {
                return [
                    'id' => $report->id,
                    'name' => $report->name ?? 'Untitled Report',
                    'generated_for' => $report->recipients ?? ($report->recipient->name ?? 'Unknown'),
                    'date_generated' => $report->last_sent ? $report->last_sent->format('Y-m-d') : 'Unknown',
                    'generated_at' => $report->last_sent ? $report->last_sent->format('Y-m-d H:i') : 'Unknown',
                    'format' => strtolower($report->format ?? 'pdf'), // Return lowercase for consistency with library
                    'size' => $report->file_size ?? $this->calculateReportSize($report), // Use stored size or calculate
                    'status' => $report->status === 'failed' ? 'failed' : 'completed',
                    'actions' => $this->generateHistoricalActionButtons($report->id)
                ];
            })
        ]);
    }

    /**
     * Store a new report schedule
     */
    public function store(Request $request)
    {
        // Debug: Log that the method was called
        \Log::info('=== REPORT STORE METHOD CALLED ===');
        \Log::info('Request method: ' . $request->method());
        \Log::info('Request URL: ' . $request->url());
        \Log::info('Request data:', $request->all());
        \Log::info('User ID: ' . (Auth::id() ?? 'NOT AUTHENTICATED'));
        \Log::info('User role: ' . (Auth::user()->role ?? 'NO ROLE'));
        
        $validator = Validator::make($request->all(), [
            'template' => 'required|string',
            'recipients' => 'required|array',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'format' => 'required|in:pdf,excel,csv',
            'schedule_time' => 'nullable|string',
            'schedule_day' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // For suppliers, enforce that they can only create reports for themselves
            $currentUser = Auth::user();
            $recipients = $request->recipients;
            
            if ($currentUser->role === 'supplier') {
                // Force recipient to be the current supplier only
                $recipients = [$currentUser->id];
            } else {
                // For admin users, map department names to user IDs
                \Log::info('Admin user, mapping recipients from:', $recipients);
                $recipients = $this->mapRecipientsToUserIds($recipients);
                \Log::info('Mapped recipients to:', $recipients);
            }

            // Ensure we have at least one valid recipient
            if (empty($recipients)) {
                \Log::error('No valid recipients found after mapping');
                return response()->json([
                    'success' => false,
                    'message' => 'No valid recipients found'
                ], 422);
            }

            $reportData = [
                'name' => $request->template,
                'description' => $this->getTemplateDescription($request->template),
                'type' => $this->mapTemplateToType($request->template),
                'recipient_id' => $recipients[0], // Taking first recipient - now guaranteed to be a user ID
                'created_by' => Auth::id(), // Set the creator
                'frequency' => $request->frequency,
                'format' => $request->format, // This should be 'excel' or 'pdf'
                'recipients' => implode(', ', $recipients),
                'schedule_time' => $request->schedule_time,
                'schedule_day' => $request->schedule_day,
                'status' => 'active',
                'content' => json_encode([
                    'template' => $request->template,
                    'filters' => $request->filters ?? [],
                    'parameters' => $request->parameters ?? []
                ])
            ];
            
            \Log::info('Report data before creation:', $reportData);

            $report = Report::create($reportData);
            
            \Log::info('Report created successfully:', ['id' => $report->id, 'format' => $report->format]);

            return response()->json([
                'success' => true,
                'message' => 'Report schedule created successfully',
                'report' => $report
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating report:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create report schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate an ad-hoc report
     */
    public function generateAdhocReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|string',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'format' => 'required|in:pdf,csv,excel',
            'filters' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $currentUser = Auth::user();
            
            // Handle recipients
            $recipients = $request->recipients ?? [];
            $recipientNames = [];
            
            if (empty($recipients)) {
                // If no recipients specified, default to current user
                $recipients = [$currentUser->id];
                $recipientNames[] = $currentUser->name;
            } else {
                // Map recipients and get their names for display
                if ($currentUser->role === 'admin') {
                    $mappedRecipients = $this->mapRecipientsToUserIds($recipients);
                    if (empty($mappedRecipients)) {
                        // If mapping fails, fallback to current user
                        \Log::warning('Recipient mapping failed, falling back to current user', ['recipients' => $recipients]);
                        $recipients = [$currentUser->id];
                        $recipientNames[] = $currentUser->name;
                    } else {
                        $recipients = $mappedRecipients;
                        $recipientNames = $recipients; // For now, use the mapped values as names
                    }
                } else {
                    $recipients = [$currentUser->id];
                    $recipientNames[] = $currentUser->name;
                }
            }
            
            // Ensure we always have at least one recipient
            if (empty($recipients)) {
                $recipients = [$currentUser->id];
                $recipientNames[] = $currentUser->name;
            }
            
            // Create a temporary report record
            $report = Report::create([
                'name' => $request->report_type . ' (' . $request->from_date . ' to ' . $request->to_date . ')',
                'type' => 'adhoc',
                'recipient_id' => $recipients[0], // Primary recipient
                'created_by' => $currentUser->id, // Track who created it
                'recipients' => implode(', ', $recipientNames), // Display names
                'frequency' => 'once',
                'format' => $request->format,
                'status' => 'processing',
                'file_size' => null, // Will be set when file is generated
                'content' => json_encode([
                    'report_type' => $request->report_type,
                    'from_date' => $request->from_date,
                    'to_date' => $request->to_date,
                    'filters' => $request->filters ?? [],
                    'delivery_method' => $request->delivery_method ?? 'download'
                ])
            ]);

            // Here you would typically dispatch a job to generate the report
            // For now, we'll simulate the process and set realistic file size
            $fileSizes = ['1.2 MB', '2.3 MB', '856 KB', '3.1 MB', '1.8 MB', '4.2 MB'];
            $randomSize = $fileSizes[array_rand($fileSizes)];
            
            $report->update([
                'status' => 'completed',
                'last_sent' => now(),
                'file_size' => $randomSize
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report generation started. You will receive an email notification when ready.',
                'report_id' => $report->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report now (on-demand)
     */
    public function generateNow(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        try {
            \Log::info('Generating report now:', ['report_id' => $report->id, 'report_name' => $report->name]);
            
            // Update the report to show it was just generated
            $report->update([
                'last_sent' => now(),
                'status' => 'active'
            ]);

            \Log::info('Report generated successfully:', ['report_id' => $report->id, 'last_sent' => $report->last_sent]);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error generating report:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a report schedule
     */
    public function destroy(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        try {
            \Log::info('Attempting to delete report: ' . $report->id . ' - ' . $report->name);
            
            $reportName = $report->name;
            $report->delete();
            
            \Log::info('Successfully deleted report: ' . $reportName);
            
            return response()->json([
                'success' => true,
                'message' => 'Report "' . $reportName . '" deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to delete report: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available report templates
     */
    public function getTemplates(Request $request)
    {
        $currentUser = Auth::user();
        
        // Check if this is a supplier-only request or if user is a supplier
        if (($request->has('supplier_only') && $request->supplier_only === 'true') || 
            $currentUser->role === 'supplier') {
            
            // Supplier-specific templates - only raw coffee related templates
            $templates = [
                [
                    'id' => 'supplier_inventory',
                    'name' => 'Supplier Inventory Report',
                    'description' => 'Raw coffee inventory levels and movements for suppliers',
                    'category' => 'Inventory'
                ],
                [
                    'id' => 'supplier_orders',
                    'name' => 'Supplier Orders Report', 
                    'description' => 'Order status and fulfillment tracking for suppliers',
                    'category' => 'Orders'
                ],
                [
                    'id' => 'supplier_quality',
                    'name' => 'Supplier Quality Report',
                    'description' => 'Raw coffee quality metrics and compliance data',
                    'category' => 'Quality'
                ],
                [
                    'id' => 'supplier_deliveries',
                    'name' => 'Supplier Delivery Report',
                    'description' => 'Delivery schedules and performance tracking',
                    'category' => 'Logistics'
                ]
            ];
        } else {
            // Admin templates - full access to all reports
            $templates = [
                [
                    'id' => 'monthly_supplier_demand',
                    'name' => 'Monthly Supplier Demand Forecast',
                    'description' => 'Comprehensive analysis of supplier demand patterns',
                    'category' => 'Supply Chain'
                ],
                [
                    'id' => 'weekly_production_efficiency',
                    'name' => 'Weekly Production Efficiency',
                    'description' => 'Production metrics and efficiency analysis',
                    'category' => 'Production'
                ],
                [
                    'id' => 'daily_retail_sales',
                    'name' => 'Daily Retail Sales Summary',
                    'description' => 'Daily sales performance across all outlets',
                    'category' => 'Sales'
                ],
                [
                    'id' => 'quarterly_quality_control',
                    'name' => 'Quarterly Quality Control Report',
                    'description' => 'Quality metrics and compliance tracking',
                    'category' => 'Quality'
                ],
                [
                    'id' => 'inventory_movement',
                    'name' => 'Inventory Movement Analysis',
                    'description' => 'Detailed inventory tracking and movement patterns',
                    'category' => 'Inventory'
                ]
            ];
        }

        return response()->json($templates);
    }

    /**
     * Get available recipients
     */
    public function getRecipients(Request $request)
    {
        $currentUser = Auth::user();
        
        // Suppliers can only send reports to themselves
        if ($currentUser->role === 'supplier') {
            return response()->json([
                'users' => [
                    [
                        'id' => $currentUser->id,
                        'name' => $currentUser->name,
                        'email' => $currentUser->email,
                        'role' => $currentUser->role
                    ]
                ],
                'internal_roles' => [], // No roles for suppliers
                'suppliers' => []       // No other suppliers
            ]);
        }

        // For admins, return all internal recipients
        $internalRoles = [
            'Finance Dept',
            'Logistics Team',
            'Production Team',
            'Sales Team',
            'Management',
            'Quality Team',
            'Compliance',
            'Warehouse Team'
        ];

        $suppliers = Supplier::select('id', 'name')->get();
        
        // Only get users with 'admin' role, excluding 'supplier' and 'vendor' roles
        $users = User::select('id', 'name', 'email', 'role')
            ->where('role', '=', 'admin')
            ->get();

        return response()->json([
            'internal_roles' => $internalRoles,
            'suppliers' => $suppliers,
            'users' => $users
        ]);
    }

    /**
     * Download a report file
     */
    public function download(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        try {
            \Log::info('Download request for report:', [
                'report_id' => $report->id,
                'report_name' => $report->name,
                'report_format' => $report->format,
                'report_format_type' => gettype($report->format)
            ]);
            
            // Generate the report content
            $reportData = $this->generateReportContent($report);
            
            $filename = $this->sanitizeFilename($report->name) . '_' . now()->format('Y-m-d') . '.' . $report->format;
            
            \Log::info('Generated filename:', ['filename' => $filename]);
            \Log::info('Format switch comparison:', ['format' => strtolower($report->format)]);
            
            switch (strtolower($report->format)) {
                case 'pdf':
                    \Log::info('Generating PDF report');
                    return $this->generatePdfReport($reportData, $filename);
                case 'csv':
                    \Log::info('Generating CSV report');
                    return $this->generateCsvReport($reportData, $filename);
                case 'excel':
                    \Log::info('Generating Excel report');
                    return $this->generateExcelReport($reportData, $filename);
                default:
                    \Log::warning('Unknown format, defaulting to PDF:', ['format' => $report->format]);
                    return $this->generatePdfReport($reportData, $filename);
            }

        } catch (\Exception $e) {
            \Log::error('Download error:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to download report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * View a report online
     */
    public function view(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return redirect()->back()->with('error', 'You are not authorized to view this report.');
        }

        try {
            // Generate the report content for viewing
            $reportData = $this->generateReportContent($report);
            
            return view('reports.view', [
                'report' => $report,
                'reportData' => $reportData,
                'generatedAt' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to view report: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified report
     */
    public function edit(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        try {
            $templates = $this->getReportTemplates();
            
            // Only get users with 'admin' role, excluding 'supplier' and 'vendor' roles
            $recipients = User::select('id', 'name', 'email', 'role')
                ->where('role', '=', 'admin')
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'report' => $report,
                    'templates' => $templates,
                    'recipients' => $recipients
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load report data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified report in storage
     */
    public function update(Request $request, Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string',
            'format' => 'required|in:pdf,excel,csv',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'recipients' => 'required|array',
            'recipients.*' => 'exists:users,id',
            'next_run' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report->update([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'format' => $request->format,
                'frequency' => $request->frequency,
                'recipients' => json_encode($request->recipients),
                'next_run' => $request->next_run,
                'parameters' => json_encode($request->parameters ?? []),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report updated successfully',
                'data' => $report->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pause a report schedule
     */
    public function pause(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        try {
            \Log::info('Attempting to pause report: ' . $report->id . ' - ' . $report->name);
            
            $report->update(['status' => 'paused']);
            
            \Log::info('Successfully paused report: ' . $report->name);
            
            return response()->json([
                'success' => true,
                'message' => 'Report "' . $report->name . '" has been paused',
                'status' => 'paused'
            ])->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            \Log::error('Failed to pause report: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to pause report: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Resume a report schedule
     */
    public function resume(Report $report)
    {
        // Check if user can access this report
        if (!$this->canAccessReport($report)) {
            return $this->unauthorizedResponse();
        }

        try {
            \Log::info('Attempting to resume report: ' . $report->id . ' - ' . $report->name);
            
            $report->update(['status' => 'active']);
            
            \Log::info('Successfully resumed report: ' . $report->name);
            
            return response()->json([
                'success' => true,
                'message' => 'Report "' . $report->name . '" has been resumed',
                'status' => 'active'
            ])->header('Content-Type', 'application/json');

        } catch (\Exception $e) {
            \Log::error('Failed to resume report: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume report: ' . $e->getMessage()
            ], 500)->header('Content-Type', 'application/json');
        }
    }

    /**
     * Get updated dashboard stats
     */
    public function getStats(Request $request)
    {
        try {
            $currentUser = Auth::user();
            $query = Report::query();
            
            // Filter based on user role
            if ($currentUser->role === 'supplier') {
                // Suppliers only see their own reports
                $query->where('created_by', $currentUser->id);
            } else {
                // Admins see all non-supplier reports (including those they created)
                $query->where(function($q) use ($currentUser) {
                    $q->where('created_by', $currentUser->id) // Reports created by current admin
                      ->orWhere(function($subQ) {
                          $subQ->whereHas('creator', function($userQuery) {
                              $userQuery->where('role', '!=', 'supplier');
                          });
                      })
                      ->orWhereNull('created_by'); // Legacy reports without creator
                });
            }
            
            $activeReports = (clone $query)->where('status', 'active')->count();
            
            // For "Generated Today", check both last_sent and completed reports created today
            $generatedTodayBySent = (clone $query)->whereDate('last_sent', today())->count();
            $generatedTodayByCreated = (clone $query)->whereDate('created_at', today())
                                                  ->whereIn('status', ['completed', 'delivered'])
                                                  ->count();
            $generatedToday = max($generatedTodayBySent, $generatedTodayByCreated);
            
            $totalReports = (clone $query)->count();
            
            // Debug logging
            \Log::info('Stats calculation debug:', [
                'user_role' => $currentUser->role,
                'user_id' => $currentUser->id,
                'total_reports' => $totalReports,
                'active_reports' => $activeReports,
                'generated_today_by_sent' => $generatedTodayBySent,
                'generated_today_by_created' => $generatedTodayByCreated,
                'generated_today_final' => $generatedToday,
                'today_date' => today()->toDateString(),
                'reports_sent_today' => (clone $query)->whereDate('last_sent', today())->get(['id', 'name', 'last_sent', 'created_by'])->toArray(),
                'reports_created_today' => (clone $query)->whereDate('created_at', today())->whereIn('status', ['completed', 'delivered'])->get(['id', 'name', 'created_at', 'status', 'created_by'])->toArray()
            ]);
            
            // Get last generated date
            $lastReport = (clone $query)->whereNotNull('last_sent')
                                       ->orderBy('last_sent', 'desc')
                                       ->first();
            $lastGenerated = $lastReport ? $lastReport->last_sent->format('M d') : 'Never';
            
            // For admins, also get pending reports and success rate
            if ($currentUser->role !== 'supplier') {
                $pendingReports = (clone $query)->where('status', 'pending')->count();
                
                // Calculate success rate (example calculation)
                $totalReports30 = (clone $query)->whereDate('created_at', '>=', now()->subDays(30))->count();
                $successfulReports = (clone $query)->whereDate('created_at', '>=', now()->subDays(30))
                                         ->where('status', '!=', 'failed')->count();
                $successRate = $totalReports30 > 0 ? round(($successfulReports / $totalReports30) * 100, 1) : 0;
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'activeReports' => $activeReports,
                        'generatedToday' => $generatedToday,
                        'pendingReports' => $pendingReports,
                        'successRate' => $successRate
                    ]
                ]);
            } else {
                // Supplier-specific stats
                return response()->json([
                    'success' => true,
                    'data' => [
                        'activeReports' => $activeReports,
                        'generatedToday' => $generatedToday,
                        'totalReports' => $totalReports,
                        'lastGenerated' => $lastGenerated
                    ]
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate report content based on report type
     */
    private function generateReportContent($report)
    {
        // Extract report parameters from content if it's ad-hoc
        $content = json_decode($report->content, true);
        $reportType = $content['report_type'] ?? $report->type;
        
        switch ($reportType) {
            case 'sales_data':
                return $this->generateSalesData($content);
            case 'inventory_movements':
                return $this->generateInventoryData($content);
            case 'order_history':
                return $this->generateOrderData($content);
            case 'production_batches':
                return $this->generateProductionData($content);
            case 'supplier_performance':
                return $this->generateSupplierData($content);
            case 'quality_metrics':
                return $this->generateQualityData($content);
            default:
                return $this->generateGenericData($report);
        }
    }

    /**
     * Generate sales data for reports
     */
    private function generateSalesData($params)
    {
        return [
            'title' => 'Sales Data Report',
            'period' => ($params['from_date'] ?? '2024-01-01') . ' to ' . ($params['to_date'] ?? '2024-12-31'),
            'summary' => [
                'total_sales' => '$125,450.00',
                'total_orders' => 342,
                'average_order_value' => '$366.81',
                'top_product' => 'Premium Arabica Blend'
            ],
            'data' => [
                ['Date', 'Product', 'Quantity', 'Revenue'],
                ['2024-01-15', 'Premium Arabica Blend', 50, '$2,500.00'],
                ['2024-01-16', 'Colombian Supreme', 30, '$1,800.00'],
                ['2024-01-17', 'Ethiopian Single Origin', 25, '$1,750.00'],
                ['2024-01-18', 'Brazilian Santos', 40, '$2,000.00'],
                ['2024-01-19', 'Guatemalan Antigua', 35, '$2,100.00']
            ]
        ];
    }

    /**
     * Generate inventory data for reports
     */
    private function generateInventoryData($params)
    {
        return [
            'title' => 'Inventory Movements Report',
            'period' => ($params['from_date'] ?? '2024-01-01') . ' to ' . ($params['to_date'] ?? '2024-12-31'),
            'summary' => [
                'total_movements' => 1247,
                'inbound_quantity' => '2,450 kg',
                'outbound_quantity' => '2,180 kg',
                'net_change' => '+270 kg'
            ],
            'data' => [
                ['Date', 'Product', 'Movement Type', 'Quantity', 'Location'],
                ['2024-01-15', 'Premium Arabica Blend', 'Inbound', '150 kg', 'Warehouse A'],
                ['2024-01-16', 'Colombian Supreme', 'Outbound', '75 kg', 'Warehouse B'],
                ['2024-01-17', 'Ethiopian Single Origin', 'Transfer', '50 kg', 'Warehouse A to B'],
                ['2024-01-18', 'Brazilian Santos', 'Inbound', '200 kg', 'Warehouse C'],
                ['2024-01-19', 'Guatemalan Antigua', 'Outbound', '100 kg', 'Warehouse A']
            ]
        ];
    }

    /**
     * Generate order data for reports
     */
    private function generateOrderData($params)
    {
        return [
            'title' => 'Order History Report',
            'period' => ($params['from_date'] ?? '2024-01-01') . ' to ' . ($params['to_date'] ?? '2024-12-31'),
            'summary' => [
                'total_orders' => 456,
                'completed_orders' => 432,
                'pending_orders' => 18,
                'cancelled_orders' => 6
            ],
            'data' => [
                ['Order ID', 'Customer', 'Date', 'Status', 'Total'],
                ['ORD-001', 'Coffee Shop ABC', '2024-01-15', 'Completed', '$1,250.00'],
                ['ORD-002', 'Retail Store XYZ', '2024-01-16', 'Completed', '$850.00'],
                ['ORD-003', 'Restaurant DEF', '2024-01-17', 'Pending', '$2,100.00'],
                ['ORD-004', 'Café GHI', '2024-01-18', 'Completed', '$675.00'],
                ['ORD-005', 'Wholesale Client', '2024-01-19', 'Completed', '$3,200.00']
            ]
        ];
    }

    /**
     * Generate production data for reports
     */
    private function generateProductionData($params)
    {
        return [
            'title' => 'Production Batches Report',
            'period' => ($params['from_date'] ?? '2024-01-01') . ' to ' . ($params['to_date'] ?? '2024-12-31'),
            'summary' => [
                'total_batches' => 89,
                'total_output' => '4,250 kg',
                'average_batch_size' => '47.8 kg',
                'efficiency_rate' => '94.2%'
            ],
            'data' => [
                ['Batch ID', 'Product', 'Date', 'Output (kg)', 'Quality Score'],
                ['BTH-001', 'Premium Arabica Blend', '2024-01-15', '45', '9.2/10'],
                ['BTH-002', 'Colombian Supreme', '2024-01-16', '50', '9.5/10'],
                ['BTH-003', 'Ethiopian Single Origin', '2024-01-17', '40', '9.8/10'],
                ['BTH-004', 'Brazilian Santos', '2024-01-18', '55', '9.1/10'],
                ['BTH-005', 'Guatemalan Antigua', '2024-01-19', '48', '9.4/10']
            ]
        ];
    }

    /**
     * Generate supplier data for reports
     */
    private function generateSupplierData($params)
    {
        return [
            'title' => 'Supplier Performance Report',
            'period' => ($params['from_date'] ?? '2024-01-01') . ' to ' . ($params['to_date'] ?? '2024-12-31'),
            'summary' => [
                'total_suppliers' => 12,
                'on_time_deliveries' => '89.5%',
                'quality_compliance' => '96.2%',
                'top_supplier' => 'Colombian Coffee Co.'
            ],
            'data' => [
                ['Supplier', 'Deliveries', 'On Time %', 'Quality Score', 'Total Value'],
                ['Colombian Coffee Co.', '24', '95%', '9.8/10', '$125,000'],
                ['Ethiopian Highlands', '18', '88%', '9.6/10', '$89,500'],
                ['Brazilian Farms Ltd.', '22', '91%', '9.4/10', '$156,200'],
                ['Guatemalan Growers', '16', '84%', '9.2/10', '$78,300'],
                ['Costa Rican Estates', '14', '93%', '9.7/10', '$95,800']
            ]
        ];
    }

    /**
     * Generate quality data for reports
     */
    private function generateQualityData($params)
    {
        return [
            'title' => 'Quality Metrics Report',
            'period' => ($params['from_date'] ?? '2024-01-01') . ' to ' . ($params['to_date'] ?? '2024-12-31'),
            'summary' => [
                'average_quality_score' => '9.4/10',
                'samples_tested' => 567,
                'passed_quality_control' => '94.2%',
                'rejected_batches' => 6
            ],
            'data' => [
                ['Date', 'Product', 'Batch ID', 'Quality Score', 'Status'],
                ['2024-01-15', 'Premium Arabica Blend', 'BTH-001', '9.2/10', 'Passed'],
                ['2024-01-16', 'Colombian Supreme', 'BTH-002', '9.5/10', 'Passed'],
                ['2024-01-17', 'Ethiopian Single Origin', 'BTH-003', '9.8/10', 'Passed'],
                ['2024-01-18', 'Brazilian Santos', 'BTH-004', '9.1/10', 'Passed'],
                ['2024-01-19', 'Guatemalan Antigua', 'BTH-005', '9.4/10', 'Passed']
            ]
        ];
    }

    /**
     * Generate generic data for existing reports
     */
    private function generateGenericData($report)
    {
        return [
            'title' => $report->name,
            'period' => 'Generated on ' . now()->format('Y-m-d H:i:s'),
            'summary' => [
                'report_type' => ucfirst($report->type),
                'format' => strtoupper($report->format),
                'frequency' => ucfirst($report->frequency),
                'status' => ucfirst($report->status)
            ],
            'data' => [
                ['Metric', 'Value', 'Period', 'Status'],
                ['Total Records', '1,247', 'Last 30 days', 'Current'],
                ['Success Rate', '94.2%', 'Last 30 days', 'Good'],
                ['Processing Time', '2.3 seconds', 'Average', 'Optimal'],
                ['Data Quality', '98.7%', 'Last 30 days', 'Excellent']
            ]
        ];
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($reportData, $filename)
    {
        // Generate HTML content
        $html = $this->generateHtmlReport($reportData);
        
        // Create PDF using DomPDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        // Return PDF download response
        return $pdf->download($filename);
    }

    /**
     * Generate CSV report
     */
    private function generateCsvReport($reportData, $filename)
    {
        $csv = "# " . $reportData['title'] . "\n";
        $csv .= "# Period: " . $reportData['period'] . "\n";
        $csv .= "# Generated: " . now()->format('Y-m-d H:i:s') . "\n\n";
        
        // Add summary
        $csv .= "Summary\n";
        foreach ($reportData['summary'] as $key => $value) {
            $csv .= ucfirst(str_replace('_', ' ', $key)) . "," . $value . "\n";
        }
        $csv .= "\n";
        
        // Add data table
        $csv .= "Detailed Data\n";
        foreach ($reportData['data'] as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response($csv, 200, $headers);
    }

    /**
     * Generate Excel report
     */
    private function generateExcelReport($reportData, $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set report title
        $sheet->setCellValue('A1', $reportData['title']);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->setCellValue('A2', 'Period: ' . $reportData['period']);
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('Y-m-d H:i:s'));
        
        $currentRow = 5;
        
        // Add summary section
        $sheet->setCellValue('A' . $currentRow, 'Summary');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $currentRow++;
        
        foreach ($reportData['summary'] as $key => $value) {
            $sheet->setCellValue('A' . $currentRow, ucfirst(str_replace('_', ' ', $key)));
            $sheet->setCellValue('B' . $currentRow, $value);
            $currentRow++;
        }
        
        $currentRow += 2; // Add some spacing
        
        // Add data table
        $sheet->setCellValue('A' . $currentRow, 'Detailed Data');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $currentRow++;
        
        // Add headers
        $col = 'A';
        foreach ($reportData['data'][0] as $header) {
            $sheet->setCellValue($col . $currentRow, $header);
            $sheet->getStyle($col . $currentRow)->getFont()->setBold(true);
            $col++;
        }
        $currentRow++;
        
        // Add data rows
        foreach (array_slice($reportData['data'], 1) as $row) {
            $col = 'A';
            foreach ($row as $cell) {
                $sheet->setCellValue($col . $currentRow, $cell);
                $col++;
            }
            $currentRow++;
        }
        
        // Auto-size columns
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Create writer and return response
        $writer = new Xlsx($spreadsheet);
        
        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Generate HTML report for viewing or PDF conversion
     */
    private function generateHtmlReport($reportData)
    {
        $html = "<!DOCTYPE html><html><head><title>{$reportData['title']}</title>";
        $html .= "<style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .header { border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            .summary { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
            .summary h3 { margin-top: 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; font-weight: bold; }
            tr:nth-child(even) { background-color: #f9f9f9; }
        </style></head><body>";
        
        $html .= "<div class='header'>";
        $html .= "<h1>{$reportData['title']}</h1>";
        $html .= "<p>Period: {$reportData['period']}</p>";
        $html .= "</div>";
        
        $html .= "<div class='summary'>";
        $html .= "<h3>Summary</h3>";
        foreach ($reportData['summary'] as $key => $value) {
            $html .= "<p><strong>" . ucfirst(str_replace('_', ' ', $key)) . ":</strong> {$value}</p>";
        }
        $html .= "</div>";
        
        $html .= "<h3>Detailed Data</h3>";
        $html .= "<table>";
        foreach ($reportData['data'] as $index => $row) {
            $html .= $index === 0 ? "<thead><tr>" : "<tr>";
            foreach ($row as $cell) {
                $html .= $index === 0 ? "<th>{$cell}</th>" : "<td>{$cell}</td>";
            }
            $html .= $index === 0 ? "</tr></thead><tbody>" : "</tr>";
        }
        $html .= "</tbody></table>";
        
        $html .= "</body></html>";
        
        return $html;
    }

    /**
     * Sanitize filename for safe download
     */
    private function sanitizeFilename($filename)
    {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
    }

    /**
     * Calculate estimated report size based on content and format
     */
    private function calculateReportSize($report)
    {
        // If file_size is already stored, return it
        if (!empty($report->file_size)) {
            return $report->file_size;
        }
        
        // This is a placeholder calculation - in real scenarios you'd calculate based on actual content
        $baseSize = 50; // Base size in KB
        
        // Add size based on format
        switch (strtolower($report->format)) {
            case 'pdf':
                $multiplier = 1.5;
                $extension = 'KB';
                break;
            case 'excel':
                $multiplier = 1.2;
                $extension = 'KB';
                break;
            case 'csv':
                $multiplier = 0.3;
                $extension = 'KB';
                break;
            default:
                $multiplier = 1.0;
                $extension = 'KB';
        }
        
        // Add some randomness to make it more realistic
        $size = round($baseSize * $multiplier * (0.8 + (rand(0, 40) / 100)), 1);
        
        // Convert to MB if size is large
        if ($size > 1024) {
            $size = round($size / 1024, 2);
            $extension = 'MB';
        }
        
        return $size . ' ' . $extension;
    }

    // Helper methods
    private function generateActionButtons($reportId)
    {
        return '
            <button class="action-btn edit-btn btn text-orange-600 hover:text-orange-900 p-1" title="Edit Schedule" data-report-id="' . $reportId . '" data-action="edit">
                <i class="fas fa-edit"></i>
            </button>
            <button class="action-btn generate-btn btn text-green-600 hover:text-green-900 p-1" title="Generate Now" data-report-id="' . $reportId . '" data-action="generate">
                <i class="fas fa-play"></i>
            </button>
            <button class="action-btn pause-btn btn text-yellow-600 hover:text-yellow-900 p-1" title="Pause Report" data-report-id="' . $reportId . '" data-action="pause">
                <i class="fas fa-pause"></i>
            </button>
            <button class="action-btn delete-btn btn text-red-600 hover:text-red-900 p-1" title="Delete" data-report-id="' . $reportId . '" data-action="delete">
                <i class="fas fa-trash"></i>
            </button>
        ';
    }

    private function generateHistoricalActionButtons($reportId)
    {
        return '
            <button class="action-btn download-btn btn text-blue-600 hover:text-blue-900 p-1" title="Download" data-report-id="' . $reportId . '" data-action="download">
                <i class="fas fa-download"></i>
            </button>
            <button class="action-btn view-btn btn text-green-600 hover:text-green-900 p-1" title="View Online" data-report-id="' . $reportId . '" data-action="view">
                <i class="fas fa-eye"></i>
            </button>
        ';
    }

    private function generateRandomSize()
    {
        $sizes = ['1.2 MB', '2.4 MB', '1.8 MB', '3.2 MB', '1.1 MB', '4.5 MB'];
        return $sizes[array_rand($sizes)];
    }

    private function getTemplateDescription($template)
    {
        $descriptions = [
            'Monthly Supplier Demand' => 'Comprehensive analysis of supplier demand patterns',
            'Weekly Production Efficiency' => 'Production metrics and efficiency analysis',
            'Daily Retail Sales' => 'Daily sales performance across all outlets',
            'Quarterly Quality Control' => 'Quality metrics and compliance tracking',
            'Inventory Movement' => 'Detailed inventory tracking and movement patterns'
        ];

        return $descriptions[$template] ?? 'Custom report template';
    }

    private function mapTemplateToType($template)
    {
        $typeMapping = [
            'Monthly Supplier Demand' => 'inventory',
            'Weekly Production Efficiency' => 'performance',
            'Daily Retail Sales' => 'order_summary',
            'Quarterly Quality Control' => 'performance',
            'Inventory Movement' => 'inventory'
        ];

        return $typeMapping[$template] ?? 'inventory';
    }

    /**
     * Check if the current user can access the specified report
     */
    private function canAccessReport(Report $report)
    {
        $currentUser = Auth::user();
        
        // Admins can access all reports
        if ($currentUser->role === 'admin') {
            return true;
        }
        
        // Suppliers can only access reports they created
        if ($currentUser->role === 'supplier') {
            return $report->created_by === $currentUser->id;
        }
        
        // Other roles have no access by default
        return false;
    }

    /**
     * Return an unauthorized response
     */
    private function unauthorizedResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'You are not authorized to access this report.'
        ], 403);
    }

    /**
     * Map department names to actual user IDs
     */
    private function mapRecipientsToUserIds(array $recipients)
    {
        $departmentMapping = [
            'admin' => 'U00001', // Default admin user
            'supplier' => null, // Will be set to current supplier user
            'Finance Dept' => 'U00001', // Admin user for now
            'Logistics Team' => 'U00001',
            'Production Team' => 'U00001', 
            'Sales Team' => 'U00001',
            'Management' => 'U00001',
            'Quality Team' => 'U00001',
            'Compliance' => 'U00001',
            'Warehouse Team' => 'U00001'
        ];
        
        $userIds = [];
        $currentUser = Auth::user();
        
        foreach ($recipients as $recipient) {
            // Check if recipient is already a user ID (starts with 'U' and is 6 chars)
            if (preg_match('/^U\d{5}$/', $recipient)) {
                // It's already a user ID, validate it exists
                if (User::where('id', $recipient)->exists()) {
                    $userIds[] = $recipient;
                }
            } elseif ($recipient === 'supplier') {
                // Map to current user for suppliers
                $userIds[] = $currentUser->id;
            } elseif (isset($departmentMapping[$recipient])) {
                // Map department name to user ID
                $userIds[] = $departmentMapping[$recipient];
            }
        }
        
        // Remove duplicates and return
        return array_unique($userIds);
    }
}
