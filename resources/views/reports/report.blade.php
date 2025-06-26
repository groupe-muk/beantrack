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
        .library-table td:nth-child(4) { width: 20%; } /* Recipients */
        .library-table th:nth-child(5),
        .library-table td:nth-child(5) { width: 13%; } /* Last Generated */
        .library-table th:nth-child(6),
        .library-table td:nth-child(6) { width: 10%; } /* Status */
        .library-table th:nth-child(7),
        .library-table td:nth-child(7) { width: 15%; } /* Actions */
        
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
        <h1 class="text-3xl font-bold text-dashboard-light">Reports</h1>           
    </div>

<!-- Stats Cards -->
<div class="px-4 sm:px-6 lg:px-8 py-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-file-alt text-blue-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Active Reports</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeReports ?? 24 }}</p>
                    <p class="text-sm text-gray-500">Scheduled reports running</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-bar text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Generated Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $generatedToday ?? 12 }}</p>
                    <p class="text-sm text-gray-500">Reports generated today</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-clock text-yellow-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Pending Reports</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingReports ?? 3 }}</p>
                    <p class="text-sm text-gray-500">Reports in queue</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-chart-line text-green-500 text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">Success Rate</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $successRate ?? 98.5 }}%</p>
                    <p class="text-sm text-gray-500">Last 30 days</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="border-b border-soft-gray">
            <nav class="-mb-px flex space-x-8">
                <button class="tab-button active text-mild-gray hover:text-warm-gray hover:border-warm-gray whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="library">
                    Report Library
                </button>
                <button class="tab-button border-transparent text-mild-gray  hover:text-warm-gray hover:border-warm-gray whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="historical">
                    Historical Reports
                </button>
                <button class="tab-button border-transparent text-mild-gray hover:text-warm-gray hover:border-warm-gray whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-tab="adhoc">
                    Ad-Hoc Generator
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
                        <h3 class="text-lg font-medium text-dashboard-light">Report Library & Configuration</h3>
                        <p class="text-sm text-soft-brown">Manage all available report templates and their scheduled deliveries</p>
                    </div>
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-light-brown hover:bg-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" onclick="openCreateReportModal()">
                        <i class="fas fa-plus mr-2"></i>
                        Create New Report Schedule
                    </button>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="px-6 py-4">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-mild-gray"></i>
                            </div>
                            <input type="text" id="library-search" class="block w-full pl-10 pr-3 py-2 border border-soft-gray rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-light-brown focus:border-light-brown" placeholder="Search reports by name or description...">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select id="library-type-filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                            <option value="all">All Types</option>
                            <option value="pdf">PDF</option>
                            <option value="excel">Excel</option>
                            <option value="dashboard">Dashboard</option>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Recipients</th>
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
                    <p class="text-sm text-soft-brown">Access previously generated reports and manage deliveries</p>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-mild-gray"></i>
                            </div>
                            <input type="text" id="historical-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-light-brown focus:border-light-brown" placeholder="Search reports by name or recipient...">
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <select id="historical-recipient-filter" class="border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                            <option value="all">All Recipients</option>
                            <option value="finance">Finance Dept</option>
                            <option value="production">Production Team</option>
                            <option value="sales">Sales Team</option>
                        </select>
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
                    <h3 class="text-lg font-medium text-dashboard-light">Ad-Hoc Report Generator</h3>
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
                                <option value="sales_data">Sales Data</option>
                                <option value="inventory_movements">Inventory Movements</option>
                                <option value="order_history">Order History</option>
                                <option value="production_batches">Production Batches</option>
                                <option value="supplier_performance">Supplier Performance</option>
                                <option value="quality_metrics">Quality Metrics</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="output-format" class="block text-sm font-medium text-gray-700">Output Format</label>
                            <select id="output-format" name="format" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                                <option value="pdf">PDF</option>
                                <option value="csv">CSV</option>
                                <option value="excel">Excel</option>
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

                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <i class="fas fa-cog mr-2"></i>
                            Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Create Report Modal -->
<div id="createReportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-dashboard-light">Create New Report Schedule</h3>
                <button class="text-mild-gray hover:text-warm-gray" onclick="closeCreateReportModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Wizard Steps -->
            <div class="py-4">
                <!-- Step Indicator -->
                <div class="flex items-center justify-center mb-8">
                    <div class="flex items-center space-x-4">
                        <div class="step-indicator flex flex-col items-center">
                            <div class="w-8 h-8 bg-soft-brown text-white rounded-full flex items-center justify-center text-sm font-medium">1</div>
                            <div class="text-xs mt-1 text-center">Template</div>
                        </div>
                        <div class="w-8 h-px bg-gray-300"></div>
                        <div class="step-indicator flex flex-col items-center">
                            <div class="w-8 h-8 bg-soft-gray text-warm-gray rounded-full flex items-center justify-center text-sm font-medium">2</div>
                            <div class="text-xs mt-1 text-center">Recipients</div>
                        </div>
                        <div class="w-8 h-px bg-soft-gray"></div>
                        <div class="step-indicator flex flex-col items-center">
                            <div class="w-8 h-8 bg-soft-gray text-warm-gray rounded-full flex items-center justify-center text-sm font-medium">3</div>
                            <div class="text-xs mt-1 text-center">Schedule</div>
                        </div>
                        <div class="w-8 h-px bg-soft-gray"></div>
                        <div class="step-indicator flex flex-col items-center">
                            <div class="w-8 h-8 bg-soft-gray text-warm-gray rounded-full flex items-center justify-center text-sm font-medium">4</div>
                            <div class="text-xs mt-1 text-center">Format</div>
                        </div>
                        <div class="w-8 h-px bg-soft-gray"></div>
                        <div class="step-indicator flex flex-col items-center">
                            <div class="w-8 h-8 bg-soft-gray text-warm-gray rounded-full flex items-center justify-center text-sm font-medium">5</div>
                            <div class="text-xs mt-1 text-center">Review</div>
                        </div>
                    </div>
                </div>

                <form id="createReportForm">
                    <!-- Step 1: Choose Template -->
                    <div class="wizard-step active" data-step="1">
                        <h4 class="text-lg font-medium text-dashboard-light mb-4">Choose Report Template</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Monthly Supplier Demand Forecast">
                                <div class="flex items-center">
                                    <i class="fas fa-chart-line text-soft-brown text-xl mr-3"></i>
                                    <div>
                                        <h5 class="font-medium text-dashboard-light">Monthly Supplier Demand Forecast</h5>
                                        <p class="text-sm text-soft-brown">Comprehensive analysis of supplier demand patterns</p>
                                    </div>
                                </div>
                            </div>
                            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Weekly Production Efficiency">
                                <div class="flex items-center">
                                    <i class="fas fa-cogs text-light-brown text-xl mr-3"></i>
                                    <div>
                                        <h5 class="font-medium text-dashboard-light">Weekly Production Efficiency</h5>
                                        <p class="text-sm text-soft-brown">Production metrics and efficiency analysis</p>
                                    </div>
                                </div>
                            </div>
                            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Daily Sales Summary">
                                <div class="flex items-center">
                                    <i class="fas fa-shopping-cart text-light-brown text-xl mr-3"></i>
                                    <div>
                                        <h5 class="font-medium text-dashboard-light">Daily Sales Summary</h5>
                                        <p class="text-sm text-soft-brown">Daily sales performance across all outlets</p>
                                    </div>
                                </div>
                            </div>
                            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Quality Control Report">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt text-light-brown text-xl mr-3"></i>
                                    <div>
                                        <h5 class="font-medium text-dashboard-light">Quality Control Report</h5>
                                        <p class="text-sm text-soft-brown">Quality metrics and compliance tracking</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Configure Recipients -->
                    <div class="wizard-step" data-step="2">
                        <h4 class="text-lg font-medium text-dashboard-light mb-4">Configure Recipients</h4>
                        <div class="space-y-3">
                            <div class="flex items-center">
                                <input type="checkbox" name="recipients[]" value="Finance Dept" class="h-4 w-4 text-light-brown focus:ring-light-brown border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Finance Department</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="recipients[]" value="Logistics Team" class="h-4 w-4 text-light-brown focus:ring-light-brown border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Logistics Team</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="recipients[]" value="Production Team" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Production Team</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="recipients[]" value="Sales Team" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Sales Team</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="recipients[]" value="Management" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Management</label>
                            </div>
                            <div class="flex items-center">
                                <input type="checkbox" name="recipients[]" value="Quality Team" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label class="ml-2 text-sm text-gray-700">Quality Team</label>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Set Schedule -->
                    <div class="wizard-step" data-step="3">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Set Schedule</h4>
                        <div class="space-y-4">
                            <div>
                                <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency</label>
                                <select id="frequency" name="frequency" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                            <div>
                                <label for="schedule-time" class="block text-sm font-medium text-gray-700">Time</label>
                                <input type="time" id="schedule-time" name="schedule_time" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                            </div>
                            <div id="schedule-day-container">
                                <label for="schedule-day" class="block text-sm font-medium text-gray-700">Day of Week/Month</label>
                                <select id="schedule-day" name="schedule_day" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Choose Format -->
                    <div class="wizard-step" data-step="4">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Choose Format</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="format-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 text-center" data-format="pdf">
                                <i class="fas fa-file-pdf text-red-500 text-3xl mb-2"></i>
                                <h5 class="font-medium text-gray-900">PDF</h5>
                                <p class="text-sm text-gray-500">Portable Document Format</p>
                            </div>
                            <div class="format-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 text-center" data-format="excel">
                                <i class="fas fa-file-excel text-green-500 text-3xl mb-2"></i>
                                <h5 class="font-medium text-gray-900">Excel</h5>
                                <p class="text-sm text-gray-500">Microsoft Excel Spreadsheet</p>
                            </div>
                            <div class="format-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-orange-300 text-center" data-format="csv">
                                <i class="fas fa-file-csv text-blue-500 text-3xl mb-2"></i>
                                <h5 class="font-medium text-gray-900">CSV</h5>
                                <p class="text-sm text-gray-500">Comma Separated Values</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 5: Review & Save -->
                    <div class="wizard-step" data-step="5">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Review & Save</h4>
                        <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <h5 class="font-medium text-gray-900 mb-2">Report Details</h5>
                                    <div class="space-y-1 text-sm">
                                        <div><span class="font-medium">Template:</span> <span id="review-template"></span></div>
                                        <div><span class="font-medium">Format:</span> <span id="review-format"></span></div>
                                    </div>
                                </div>
                                <div>
                                    <h5 class="font-medium text-gray-900 mb-2">Schedule & Recipients</h5>
                                    <div class="space-y-1 text-sm">
                                        <div><span class="font-medium">Frequency:</span> <span id="review-frequency"></span></div>
                                        <div><span class="font-medium">Time:</span> <span id="review-time"></span></div>
                                        <div><span class="font-medium">Day:</span> <span id="review-day"></span></div>
                                        <div><span class="font-medium">Recipients:</span> <span id="review-recipients"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Modal Footer -->
                <div class="flex justify-between items-center pt-6 mt-6 border-t">
                    <button type="button" id="prev-btn" class="px-4 py-2 text-sm font-medium text-mild-gray bg-light-background rounded-md hover:bg-gray-300 hidden" onclick="previousStep()">
                        Previous
                    </button>
                    <div class="flex space-x-2">
                        <button type="button" class="px-4 py-2 text-sm font-medium text-mild-gray bg-light-background rounded-md hover:bg-gray-300" onclick="closeCreateReportModal()">
                            Cancel
                        </button>
                        <button type="button" id="next-btn" class="px-4 py-2 bg-light-brown text-white rounded-md hover:bg-brown" onclick="nextStep()">
                            Next
                        </button>
                        <button type="button" id="save-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 hidden" onclick="saveReportSchedule()">
                            Save Schedule
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/reports.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
@endpush
