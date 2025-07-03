<form id="createReportForm">
    <!-- Step 1: Choose Template -->
    <div class="wizard-step active" data-step="1">
        <h4 class="text-lg font-medium text-dashboard-light mb-4">Choose Report Template</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Supplier Inventory Report">
                <div class="flex items-center">
                    <i class="fas fa-boxes text-soft-brown text-xl mr-3"></i>
                    <div>
                        <h5 class="font-medium text-dashboard-light">Supplier Inventory Report</h5>
                        <p class="text-sm text-soft-brown">Current inventory levels and stock movements</p>
                    </div>
                </div>
            </div>
            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Supplier Orders Report">
                <div class="flex items-center">
                    <i class="fas fa-shopping-cart text-light-brown text-xl mr-3"></i>
                    <div>
                        <h5 class="font-medium text-dashboard-light">Supplier Orders Report</h5>
                        <p class="text-sm text-soft-brown">Order status and fulfillment tracking</p>
                    </div>
                </div>
            </div>
            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Supplier Quality Report">
                <div class="flex items-center">
                    <i class="fas fa-shield-alt text-light-brown text-xl mr-3"></i>
                    <div>
                        <h5 class="font-medium text-dashboard-light">Supplier Quality Report</h5>
                        <p class="text-sm text-soft-brown">Quality metrics and compliance data</p>
                    </div>
                </div>
            </div>
            <div class="template-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-template="Supplier Delivery Report">
                <div class="flex items-center">
                    <i class="fas fa-truck text-light-brown text-xl mr-3"></i>
                    <div>
                        <h5 class="font-medium text-dashboard-light">Supplier Delivery Report</h5>
                        <p class="text-sm text-soft-brown">Delivery schedules and performance</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 2: Schedule Configuration -->
    <div class="wizard-step" data-step="2">
        <h4 class="text-lg font-medium text-dashboard-light mb-4">Schedule Configuration</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="frequency" class="block text-sm font-medium text-dashboard-light mb-2">Frequency *</label>
                <select id="frequency" name="frequency" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                    <option value="">Select frequency...</option>
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                    <option value="quarterly">Quarterly</option>
                </select>
            </div>
            <div>
                <label for="schedule-time" class="block text-sm font-medium text-dashboard-light mb-2">Time</label>
                <input type="time" id="schedule-time" name="schedule_time" value="08:00" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
            </div>
        </div>
        <div class="mt-4">
            <label for="schedule-day" class="block text-sm font-medium text-dashboard-light mb-2">Day (for weekly/monthly)</label>
            <select id="schedule-day" name="schedule_day" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                <option value="monday">Monday</option>
                <option value="tuesday">Tuesday</option>
                <option value="wednesday">Wednesday</option>
                <option value="thursday">Thursday</option>
                <option value="friday">Friday</option>
                <option value="saturday">Saturday</option>
                <option value="sunday">Sunday</option>
                <option value="1">1st of Month</option>
                <option value="15">15th of Month</option>
                <option value="last">Last Day of Month</option>
            </select>
        </div>
    </div>

    <!-- Step 3: Format Selection -->
    <div class="wizard-step" data-step="3">
        <h4 class="text-lg font-medium text-dashboard-light mb-4">Report Format</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="format-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-format="pdf">
                <div class="flex items-center">
                    <i class="fas fa-file-pdf text-red-500 text-2xl mr-3"></i>
                    <div>
                        <h5 class="font-medium text-dashboard-light">PDF Document</h5>
                        <p class="text-sm text-soft-brown">Professional formatted report</p>
                    </div>
                </div>
            </div>
            <div class="format-option border-2 border-gray-200 rounded-lg p-4 cursor-pointer hover:border-light-brown" data-format="excel">
                <div class="flex items-center">
                    <i class="fas fa-file-excel text-green-500 text-2xl mr-3"></i>
                    <div>
                        <h5 class="font-medium text-dashboard-light">Excel Spreadsheet</h5>
                        <p class="text-sm text-soft-brown">Data analysis and manipulation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Step 4: Review and Confirm -->
    <div class="wizard-step" data-step="4">
        <h4 class="text-lg font-medium text-dashboard-light mb-4">Review Your Report Schedule</h4>
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h6 class="font-medium text-dashboard-light">Template</h6>
                    <p id="review-template" class="text-soft-brown">-</p>
                </div>
                <div>
                    <h6 class="font-medium text-dashboard-light">Format</h6>
                    <p id="review-format" class="text-soft-brown">-</p>
                </div>
                <div>
                    <h6 class="font-medium text-dashboard-light">Frequency</h6>
                    <p id="review-frequency" class="text-soft-brown">-</p>
                </div>
                <div>
                    <h6 class="font-medium text-dashboard-light">Schedule</h6>
                    <p id="review-schedule" class="text-soft-brown">-</p>
                </div>
                <div>
                    <h6 class="font-medium text-dashboard-light">Recipient</h6>
                    <p id="review-recipient" class="text-soft-brown">{{ Auth::user()->name ?? 'Current User' }}</p>
                </div>
            </div>
        </div>
        <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                <p class="text-sm text-blue-700">This report will be automatically delivered to your account. You can modify or pause the schedule at any time from the Report Library.</p>
            </div>
        </div>
    </div>
</form>
