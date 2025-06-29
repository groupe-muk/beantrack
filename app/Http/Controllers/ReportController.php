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

class ReportController extends Controller
{
    /**
     * Display the main reports dashboard
     */
    public function index()
    {
        $activeReports = Report::where('status', 'active')->count();
        $generatedToday = Report::whereDate('last_sent', today())->count();
        $pendingReports = Report::where('status', 'pending')->count();
        
        // Calculate success rate (example calculation)
        $totalReports = Report::whereDate('created_at', '>=', now()->subDays(30))->count();
        $successfulReports = Report::whereDate('created_at', '>=', now()->subDays(30))
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
     * Get report library data for DataTables
     */
    public function getReportLibrary(Request $request)
    {
        $query = Report::with('recipient');

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
                    'frequency' => ucfirst($report->frequency),
                    'recipients' => $report->recipients ?? 'Not specified',
                    'last_generated' => $report->last_sent ? $report->last_sent->format('Y-m-d') : 'Never',
                    'status' => $report->status ?? 'active',
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
        $query = Report::with('recipient')->whereNotNull('last_sent');

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
                    'generated_for' => $report->recipient->name ?? 'Unknown',
                    'date_generated' => $report->last_sent->format('Y-m-d H:i'),
                    'format' => strtoupper($report->format ?? 'PDF'),
                    'size' => $this->generateRandomSize(), // Placeholder
                    'status' => $report->status === 'failed' ? 'Failed' : 'Success',
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
        $validator = Validator::make($request->all(), [
            'template' => 'required|string',
            'recipients' => 'required|array',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly',
            'format' => 'required|in:pdf,excel',
            'schedule_time' => 'nullable|string',
            'schedule_day' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $report = Report::create([
                'name' => $request->template,
                'description' => $this->getTemplateDescription($request->template),
                'type' => $this->mapTemplateToType($request->template),
                'recipient_id' => $request->recipients[0], // Taking first recipient for now
                'frequency' => $request->frequency,
                'format' => $request->format,
                'recipients' => implode(', ', $request->recipients),
                'schedule_time' => $request->schedule_time,
                'schedule_day' => $request->schedule_day,
                'status' => 'active',
                'content' => json_encode([
                    'template' => $request->template,
                    'filters' => $request->filters ?? [],
                    'parameters' => $request->parameters ?? []
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report schedule created successfully',
                'report' => $report
            ]);

        } catch (\Exception $e) {
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
            // Create a temporary report record
            $report = Report::create([
                'name' => $request->report_type . ' (' . $request->from_date . ' to ' . $request->to_date . ')',
                'type' => 'adhoc',
                'recipient_id' => Auth::id(),
                'frequency' => 'once',
                'format' => $request->format,
                'status' => 'processing',
                'content' => json_encode([
                    'report_type' => $request->report_type,
                    'from_date' => $request->from_date,
                    'to_date' => $request->to_date,
                    'filters' => $request->filters ?? []
                ])
            ]);

            // Here you would typically dispatch a job to generate the report
            // For now, we'll simulate the process
            $report->update([
                'status' => 'completed',
                'last_sent' => now()
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
        try {
            // Here you would implement the actual report generation logic
            // For now, we'll simulate the process
            
            $report->update([
                'last_sent' => now(),
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Report generated successfully'
            ]);

        } catch (\Exception $e) {
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
        try {
            $report->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Report schedule deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available report templates
     */
    public function getTemplates()
    {
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

        return response()->json($templates);
    }

    /**
     * Get available recipients
     */
    public function getRecipients()
    {
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
        $users = User::select('id', 'name', 'email')->get();

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
        try {
            // Here you would implement the actual file download logic
            // For now, we'll create a sample file response
            
            $filename = $report->name . '_' . $report->last_sent->format('Y-m-d') . '.' . $report->format;
            $headers = [
                'Content-Type' => $this->getContentType($report->format),
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            // For demonstration, return a simple text content
            $content = "Sample report content for: " . $report->name . "\nGenerated: " . $report->last_sent;
            
            return response($content, 200, $headers);

        } catch (\Exception $e) {
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
        try {
            // Here you would implement the actual report viewing logic
            // For now, we'll return a simple view
            
            return response()->json([
                'success' => true,
                'report' => [
                    'id' => $report->id,
                    'name' => $report->name,
                    'content' => 'Sample report content for viewing',
                    'generated_at' => $report->last_sent
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to view report: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getContentType($format)
    {
        $contentTypes = [
            'pdf' => 'application/pdf',
            'excel' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'csv' => 'text/csv'
        ];

        return $contentTypes[$format] ?? 'application/octet-stream';
    }

    // Helper methods
    private function generateActionButtons($reportId)
    {
        return '
            <button class="btn text-orange-600 hover:text-orange-900 p-1" title="Edit Schedule" onclick="editReport(' . $reportId . ')">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn text-green-600 hover:text-green-900 p-1" title="Generate Now" onclick="generateNow(' . $reportId . ')">
                <i class="fas fa-play"></i>
            </button>
            <button class="btn text-blue-600 hover:text-blue-900 p-1" title="View Template" onclick="viewTemplate(' . $reportId . ')">
                <i class="fas fa-eye"></i>
            </button>
            <button class="btn text-red-600 hover:text-red-900 p-1" title="Delete" onclick="deleteReport(' . $reportId . ')">
                <i class="fas fa-trash"></i>
            </button>
        ';
    }

    private function generateHistoricalActionButtons($reportId)
    {
        return '
            <button class="btn text-blue-600 hover:text-blue-900 p-1" title="Download" onclick="downloadReport(' . $reportId . ')">
                <i class="fas fa-download"></i>
            </button>
            <button class="btn text-green-600 hover:text-green-900 p-1" title="View Online" onclick="viewReport(' . $reportId . ')">
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
            'Monthly Supplier Demand Forecast' => 'Comprehensive analysis of supplier demand patterns',
            'Weekly Production Efficiency' => 'Production metrics and efficiency analysis',
            'Daily Retail Sales Summary' => 'Daily sales performance across all outlets',
            'Quarterly Quality Control Report' => 'Quality metrics and compliance tracking',
            'Inventory Movement Analysis' => 'Detailed inventory tracking and movement patterns'
        ];

        return $descriptions[$template] ?? 'Custom report template';
    }

    private function mapTemplateToType($template)
    {
        $typeMapping = [
            'Monthly Supplier Demand Forecast' => 'inventory',
            'Weekly Production Efficiency' => 'performance',
            'Daily Retail Sales Summary' => 'order_summary',
            'Quarterly Quality Control Report' => 'performance',
            'Inventory Movement Analysis' => 'inventory'
        ];

        return $typeMapping[$template] ?? 'inventory';
    }
}
