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
        <div id="recipients-container" class="space-y-3">
            <!-- Recipients will be loaded dynamically via JavaScript -->
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin text-gray-400"></i>
                <p class="text-gray-500 mt-2">Loading recipients...</p>
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
