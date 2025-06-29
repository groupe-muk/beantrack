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

        <x-stats-card
        id="active-reports-card"
        title="Active Reports"
        value="{{ $activeReports ?? 24}}"
        iconClass="fa-file-alt"
        changeText="Scheduled Reports running"
        />

        <x-stats-card
        id="generated-today-card"
        title="Generated Today"
        value="{{ $generatedToday ?? 12}}"
        iconClass="fa-chart-bar"
        changeText="Reports generated today"
        />

        <x-stats-card
        id="pending-reports-card"
        title="Pending Reports"
        value="{{ $pendingReports ?? 3 }}"
        iconClass="fa-clock"
        changeText="Reports in queue"
        />

        <x-stats-card
        id="success-rate-card"
        title="Success Rate"
        value="{{ $successRate ?? 98.5 }}%"
        iconClass="fa-chart-line"
        changeText="Last 30 days"
        />

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
                    <div class="flex gap-3">
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown" onclick="openRecipientsModal()">
                            <i class="fas fa-users mr-2"></i>
                            Manage Recipients
                        </button>
                        <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-light-brown hover:bg-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown" onclick="openCreateReportModal()">
                            <i class="fas fa-plus mr-2"></i>
                            Create New Report Schedule
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

                    <!-- Delivery Options Section -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-medium text-gray-700">Delivery Options</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Method</label>
                                <div class="space-y-2">
                                    <label class="flex items-center">
                                        <input type="radio" name="delivery_method" value="download" class="mr-2" checked>
                                        <span class="text-sm text-gray-700">Download immediately</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="delivery_method" value="email" class="mr-2">
                                        <span class="text-sm text-gray-700">Send via email</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="delivery_method" value="both" class="mr-2">
                                        <span class="text-sm text-gray-700">Download and email</span>
                                    </label>
                                </div>
                            </div>
                            
                            <div id="email-recipients-section" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Recipients</label>
                                <div class="border border-gray-300 rounded-md p-3 max-h-48 overflow-y-auto bg-white">
                                    <div class="space-y-2" id="recipients-checkbox-list">
                                        <!-- Recipients checkboxes will be loaded dynamically -->
                                    </div>
                                </div>
                                <div class="flex justify-between items-center mt-2">
                                    <p class="text-sm text-gray-500">Select one or more recipients</p>
                                    <div class="space-x-2">
                                        <button type="button" id="select-all-recipients" class="text-xs text-orange-600 hover:text-orange-800">Select All</button>
                                        <span class="text-gray-300">|</span>
                                        <button type="button" id="clear-all-recipients" class="text-xs text-orange-600 hover:text-orange-800">Clear All</button>
                                    </div>
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

<!-- Create Report Modal -->
<x-wizard-modal 
    id="createReportModal" 
    title="Create New Report Schedule"
    :steps="['Template', 'Recipients', 'Schedule', 'Format', 'Review']">
    <x-reports.wizard-content />
</x-wizard-modal>

<!-- Recipients Management Modal -->
<div id="recipientsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Manage Recipients</h3>
                <button id="close-recipients-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="text-md font-medium text-gray-900">Report Recipients</h4>
                        <p class="text-sm text-gray-500">Add, edit, or remove recipients for report deliveries</p>
                    </div>
                    <button id="add-recipient-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-light-brown hover:bg-brown">
                        <i class="fas fa-plus mr-2"></i>
                        Add Recipient
                    </button>
                </div>
                
                <!-- Recipients Table -->
                <div class="overflow-x-auto border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recipients-tbody" class="bg-white divide-y divide-gray-200">
                            <!-- Recipients will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Recipient Modal -->
<div id="recipientFormModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 id="recipient-form-title" class="text-lg font-semibold text-gray-900">Add Recipient</h3>
                <button id="close-recipient-form-modal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="recipient-form" class="p-6">
                <input type="hidden" id="recipient-id" name="recipient_id">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                        <input type="text" id="recipient-name" name="name" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="recipient-email" name="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                        <select id="recipient-department" name="department" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                            <option value="">Select Department</option>
                            <option value="Finance">Finance</option>
                            <option value="Production">Production</option>
                            <option value="Sales">Sales</option>
                            <option value="Logistics">Logistics</option>
                            <option value="Operations">Operations</option>
                            <option value="Management">Management</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="recipient-status" name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                    <button type="button" id="cancel-recipient" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                        Cancel
                    </button>
                    <button type="submit" id="save-recipient" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md">
                        Save Recipient
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
                            <i class="fas fa-cog fa-2x text-orange-600 animate-spin"></i>
                        </div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Generating Report...</h4>
                    <p class="text-sm text-gray-500 mb-4">Please wait while we process your request</p>
                    <div class="w-full bg-gray-200 rounded-full h-2 mb-4">
                        <div id="progress-bar" class="bg-orange-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-sm text-gray-600">Initializing...</p>
                </div>
                
                <!-- Success Section -->
                <div id="generation-success" class="text-center hidden">
                    <div class="mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full">
                            <i class="fas fa-check fa-2x text-green-600"></i>
                        </div>
                    </div>
                    <h4 class="text-lg font-medium text-gray-900 mb-2">Report Generated Successfully!</h4>
                    <p class="text-sm text-gray-500 mb-6">Your report has been generated and is ready for download</p>
                    
                    <!-- Report Details -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500">Report Type:</span>
                                <span id="result-type" class="font-medium text-gray-900 ml-2">Sales Data</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Format:</span>
                                <span id="result-format" class="font-medium text-gray-900 ml-2">PDF</span>
                            </div>
                            <div>
                                <span class="text-gray-500">File Size:</span>
                                <span id="result-size" class="font-medium text-gray-900 ml-2">2.3 MB</span>
                            </div>
                            <div>
                                <span class="text-gray-500">Generated:</span>
                                <span id="result-time" class="font-medium text-gray-900 ml-2">Just now</span>
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
                            View Report
                        </button>
                    </div>
                    
                    <div id="email-sent-notice" class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md hidden">
                        <div class="flex items-center">
                            <i class="fas fa-envelope text-blue-600 mr-2"></i>
                            <span class="text-sm text-blue-800">Report has been sent to the selected recipients</span>
                        </div>
                    </div>
                </div>
                
                <!-- Error Section -->
                <div id="generation-error" class="text-center hidden">
                    <div class="mb-4">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full">
                            <i class="fas fa-exclamation-triangle fa-2x text-red-600"></i>
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
<script src="{{ asset('js/reports.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.1/flowbite.min.js"></script>
@endpush
