@extends('layouts.main-view')

@push('styles')
    <style>
        .tab-content { 
            display: none !important; 
        }
        .tab-content.active { 
            display: block !important; 
        }
        .tab-button.active { 
            border-color: var(--color-light-brown) !important; 
            color: var(--color-light-brown) !important; 
        }
        .wizard-step { 
            display: none !important; 
        }
        .wizard-step.active { 
            display: block !important; 
        }
        
        /* Enhanced Table Layout */
        .reports-table {
            table-layout: fixed;
            width: 100%;
        }
        
        .reports-table th,
        .reports-table td {
            word-wrap: break-word;
            overflow-wrap: break-word;
            padding: 0.75rem 1rem;
            vertical-align: top;
        }
        
        /* Column widths for library table */
        .library-table-container {
            min-width: 900px;
        }
        
        .library-table th:nth-child(1),
        .library-table td:nth-child(1) { width: 25%; } /* Report Name */
        .library-table th:nth-child(2),
        .library-table td:nth-child(2) { width: 15%; } /* Type */
        .library-table th:nth-child(3),
        .library-table td:nth-child(3) { width: 12%; } /* Frequency */
        .library-table th:nth-child(4),
        .library-table td:nth-child(4) { width: 13%; } /* Last Generated */
        .library-table th:nth-child(5),
        .library-table td:nth-child(5) { width: 10%; } /* Status */
        .library-table th:nth-child(6),
        .library-table td:nth-child(6) { width: 25%; } /* Actions */
        
        /* Column widths for historical table */
        .historical-table-container {
            min-width: 1000px;
        }
        
        .historical-table th:nth-child(1),
        .historical-table td:nth-child(1) { width: 25%; } /* Report Name */
        .historical-table th:nth-child(2),
        .historical-table td:nth-child(2) { width: 20%; } /* Generated For */
        .historical-table th:nth-child(3),
        .historical-table td:nth-child(3) { width: 15%; } /* Date Generated */
        .historical-table th:nth-child(4),
        .historical-table td:nth-child(4) { width: 10%; } /* Format */
        .historical-table th:nth-child(5),
        .historical-table td:nth-child(5) { width: 10%; } /* Size */
        .historical-table th:nth-child(6),
        .historical-table td:nth-child(6) { width: 10%; } /* Status */
        .historical-table th:nth-child(7),
        .historical-table td:nth-child(7) { width: 10%; } /* Actions */
        
        /* Text handling */
        .truncate-text {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
        }
        
        .wrap-text {
            word-break: break-word;
            white-space: normal;
            line-height: 1.4;
        }
        
        /* Action buttons container */
        .action-buttons {
            display: flex;
            gap: 0.25rem;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: wrap;
            max-width: 100%;
        }
        
        .action-buttons .btn {
            flex-shrink: 0;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }
        
        /* Responsive table container */
        .table-container {
            overflow-x: auto;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
        }
        
        /* Badge sizing */
        .badge {
            display: inline-flex;
            align-items: center;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            max-width: 100%;
        }
        
        .badge i {
            margin-right: 0.25rem;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')

<!-- Header -->
    <div class="px-4 sm:px-6 lg:px-8 p-5">
        <h1 class="text-3xl font-bold text-dashboard-light">My Reports</h1>           
        <p class="text-sm text-soft-brown mt-2">View and download reports generated for your account</p>
    </div>

<!-- Stats Cards -->
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <x-stats-card
        id="active-reports-card"
        title="Active Reports"
        value="{{ $activeReports ?? 0}}"
        iconClass="fa-file-alt"
        changeText="Scheduled Reports for you"
        />

        <x-stats-card
        id="generated-today-card"
        title="Generated Today"
        value="{{ $generatedToday ?? 0}}"
        iconClass="fa-chart-bar"
        changeText="Reports generated today"
        />

        <x-stats-card
        id="total-reports-card"
        title="Total Reports"
        value="{{ $totalReports ?? 0 }}"
        iconClass="fa-archive"
        changeText="All time reports"
        />

        <x-stats-card
        id="latest-report-card"
        title="Latest Report"
        value="{{ $latestReportDate ?? 'None' }}"
        iconClass="fa-clock"
        changeText="Most recent delivery"
        />

    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="border-b border-soft-gray">
            <nav class="-mb-px flex space-x-8">
                <button class="tab-button active text-mild-gray hover:text-warm-gray hover:border-warm-gray whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="library">
                    My Report Schedules
                </button>
                <button class="tab-button border-transparent text-mild-gray  hover:text-warm-gray hover:border-warm-gray whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="historical">
                    Historical Reports
                </button>
                <button class="tab-button border-transparent text-mild-gray hover:text-warm-gray hover:border-warm-gray whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="adhoc">
                    Request Report
                </button>
            </nav>
        </div>
    </div>

    <!-- Report Library Tab -->
    <div id="library-tab" class="tab-content active">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-dashboard-light">My Report Schedules</h3>
                        <p class="text-sm text-soft-brown">View all report schedules configured for your account</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="create-schedule-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-light-brown hover:bg-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown">
                            <i class="fas fa-plus mr-2"></i>
                            Create Report Schedule
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="library-search" class="block w-full pl-10 pr-3 py-2 border border-soft-gray rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-light-brown focus:border-light-brown" placeholder="Search reports by name or description...">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select id="library-type-filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                            <option value="all">All Types</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                        </select>
                        <select id="library-frequency-filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                            <option value="all">All Frequencies</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Reports Table -->
            <div class="table-container library-table-container">
                <table class="min-w-full divide-y divide-gray-200 reports-table library-table" id="library-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Generated</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="library-tbody">
                        <!-- Table content will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Historical Reports Tab -->
    <div id="historical-tab" class="tab-content">
        <div class="bg-white shadow">
            <div class="px-6 py-4">
                <div>
                    <h3 class="text-lg font-medium text-dashboard-light">Historical Reports Archive</h3>
                    <p class="text-sm text-soft-brown">Access previously generated reports delivered to your account</p>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                            <input type="text" id="historical-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-light-brown focus:border-light-brown" placeholder="Search reports by name...">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <input type="date" id="historical-from-date" class="border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown" placeholder="From">
                        <input type="date" id="historical-to-date" class="border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown" placeholder="To">
                    </div>
                </div>
            </div>

            <!-- Historical Reports Table -->
            <div class="table-container historical-table-container">
                <table class="min-w-full divide-y divide-gray-200 reports-table historical-table" id="historical-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Generated For</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Generated</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="historical-tbody">
                        <!-- Table content will be loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ad-Hoc Generator Tab -->
    <div id="adhoc-tab" class="tab-content">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-medium text-dashboard-light">Request Custom Report</h3>
                    <p class="text-sm text-soft-brown">Generate custom reports for specific data sets and time periods</p>
                </div>
            </div>
            
            <div class="p-6">
                <form id="adhoc-form" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="report-type" class="block text-sm font-medium text-gray-700">Report Type</label>
                            <select id="report-type" name="report_type" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                                <option value="">Select Report Type</option>
                                <option value="vendor_purchases">My Purchases Report</option>
                                <option value="vendor_orders">My Orders Report</option>
                                <option value="vendor_deliveries">My Deliveries Report</option>
                                <option value="vendor_payments">My Payments Report</option>
                                <option value="vendor_inventory">My Inventory Report</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="output-format" class="block text-sm font-medium text-gray-700">Output Format</label>
                            <select id="output-format" name="format" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                                <option value="pdf">PDF</option>
                                <option value="excel">Excel</option>
                                <option value="csv">CSV</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="from-date" class="block text-sm font-medium text-gray-700">From Date</label>
                            <input type="date" id="from-date" name="from_date" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                        </div>
                        
                        <div>
                            <label for="to-date" class="block text-sm font-medium text-gray-700">To Date</label>
                            <input type="date" id="to-date" name="to_date" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                        </div>
                    </div>

                    <!-- Dynamic Filters Section -->
                    <div id="dynamic-filters" class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700">Filters</h4>
                        <div id="filters-container" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Dynamic filters will be populated based on report type -->
                        </div>
                    </div>

                    <!-- Delivery Options Section -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700">Delivery Options</h4>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Method</label>
                                <div class="space-y-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="delivery_method" value="download" class="form-radio text-light-brown focus:ring-light-brown" checked>
                                        <span class="ml-2 text-sm text-gray-700">Download Only</span>
                                    </label>
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="delivery_method" value="email" class="form-radio text-light-brown focus:ring-light-brown">
                                        <span class="ml-2 text-sm text-gray-700">Email to me</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-light-brown hover:bg-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i class="fas fa-cog mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Wizard Modal for creating/editing schedules -->
<x-wizard-modal 
    id="createReportModal" 
    title="Create Report Schedule"
    :steps="['Template', 'Schedule', 'Format', 'Review']"
    closeFunction="closeCreateScheduleModal"
    saveFunction="saveVendorReportSchedule">
    <x-reports.vendor-wizard-content />
</x-wizard-modal>

<!-- Ad-hoc Report Generation Modal -->
<div id="adhocGenerationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Report Generation</h3>
                <button id="close-adhoc-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <!-- Progress Section -->
                <div id="generation-progress" class="text-center">
                    <div class="mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full">
                            <i class="fas fa-cog fa-spin text-2xl text-light-brown"></i>
                        </div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Generating Report...</h4>
                    <p class="text-sm text-gray-500 mb-4">Please wait while we process your request</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                        <div id="progress-bar" class="bg-light-brown h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-sm text-gray-600">Initializing...</p>
                </div>
                
                <!-- Success Section -->
                <div id="generation-success" class="text-center hidden">
                    <div class="mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                            <i class="fas fa-check text-2xl text-green-600"></i>
                        </div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Report Generated Successfully!</h4>
                    <p class="text-sm text-gray-500 mb-6">Your report has been generated and is ready for download</p>
                    
                    <!-- Report Details -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="text-left">
                                <span class="font-medium text-gray-600">Type:</span>
                                <span id="result-type" class="text-gray-900">-</span>
                            </div>
                            <div class="text-left">
                                <span class="font-medium text-gray-600">Format:</span>
                                <span id="result-format" class="text-gray-900">-</span>
                            </div>
                            <div class="text-left">
                                <span class="font-medium text-gray-600">Size:</span>
                                <span id="result-size" class="text-gray-900">-</span>
                            </div>
                            <div class="text-left">
                                <span class="font-medium text-gray-600">Generated:</span>
                                <span id="result-time" class="text-gray-900">-</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex justify-center space-x-3">
                        <button id="download-report-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700">
                            <i class="fas fa-download mr-2"></i>
                            Download Report
                        </button>
                        <button id="view-report-btn" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-eye mr-2"></i>
                            View Online
                        </button>
                    </div>
                    
                    <div id="email-sent-notice" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md hidden">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-400 mr-2"></i>
                            <span class="text-sm text-blue-700">Report has been sent to your email address</span>
                        </div>
                    </div>
                </div>
                
                <!-- Error Section -->
                <div id="generation-error" class="text-center hidden">
                    <div class="mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full">
                            <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                        </div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Generation Failed</h4>
                    <p id="error-message" class="text-sm text-gray-500 mb-6">An error occurred while generating the report</p>
                    <button id="retry-generation-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700">
                        <i class="fas fa-redo mr-2"></i>
                        Try Again
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/vendor-reports.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
@endpush
