// Global variables
let currentStep = 1;
let selectedTemplate = '';
let selectedFormat = '';

// Store current data for filtering
let currentLibraryData = [];
let currentHistoricalData = [];

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    try {
        console.log('Vendor reports JS loaded');
        
        // Add wizard styles to head
        if (!document.querySelector('#wizard-styles')) {
            const styleElement = document.createElement('style');
            styleElement.id = 'wizard-styles';
            styleElement.textContent = `
                .wizard-step {
                    display: none !important;
                }
                .wizard-step.active {
                    display: block !important;
                }
                .action-buttons {
                    display: flex;
                    gap: 8px;
                    align-items: center;
                }
                .action-buttons button {
                    padding: 4px 8px;
                    border: none;
                    background: none;
                    cursor: pointer;
                    border-radius: 4px;
                    transition: all 0.2s;
                }
                .action-buttons button:hover {
                    background-color: rgba(0, 0, 0, 0.05);
                }
            `;
            document.head.appendChild(styleElement);
            console.log('Wizard styles added');
        }
        
        setupTabNavigation();
        loadReportLibrary();
        loadHistoricalReports();
        setupAdhocForm();
        setupCreateScheduleModal();
        setupActionButtonHandlers();
        setupSearchAndFilters();
        
        // Update stats cards on page load
        updateStatsCards();
        
        console.log('All setup functions completed successfully');
    } catch (error) {
        console.error('Error during initialization:', error);
    }
});

// Make functions globally accessible for onclick handlers
window.saveVendorReportSchedule = saveVendorReportSchedule;
window.closeCreateScheduleModal = closeCreateScheduleModal;
window.nextStep = nextStep;
window.previousStep = previousStep;

// Search and Filters Setup
function setupSearchAndFilters() {
    // Library search
    const librarySearch = document.getElementById('library-search');
    if (librarySearch) {
        librarySearch.addEventListener('input', filterLibraryTable);
    }

    // Library filters
    const typeFilter = document.getElementById('library-type-filter');
    const frequencyFilter = document.getElementById('library-frequency-filter');
    
    if (typeFilter) {
        typeFilter.addEventListener('change', filterLibraryTable);
    }
    
    if (frequencyFilter) {
        frequencyFilter.addEventListener('change', filterLibraryTable);
    }

    // Historical search and date filters
    const historicalSearch = document.getElementById('historical-search');
    const fromDate = document.getElementById('historical-from-date');
    const toDate = document.getElementById('historical-to-date');
    
    if (historicalSearch) {
        historicalSearch.addEventListener('input', filterHistoricalTable);
    }
    if (fromDate) {
        fromDate.addEventListener('change', filterHistoricalTable);
    }
    if (toDate) {
        toDate.addEventListener('change', filterHistoricalTable);
    }
}

function filterLibraryTable() {
    const searchTerm = document.getElementById('library-search')?.value.toLowerCase() || '';
    const typeFilter = document.getElementById('library-type-filter')?.value || 'all';
    const frequencyFilter = document.getElementById('library-frequency-filter')?.value || 'all';
    
    const filteredData = currentLibraryData.filter(report => {
        const nameMatch = report.name.toLowerCase().includes(searchTerm);
        const typeMatch = typeFilter === 'all' || report.format === typeFilter;
        
        // Handle case-insensitive frequency matching
        // Backend sends "Daily", "Weekly", etc. but filter has "daily", "weekly"
        const reportFreq = (report.frequency || '').toLowerCase();
        const filterFreq = frequencyFilter.toLowerCase();
        const frequencyMatch = frequencyFilter === 'all' || reportFreq === filterFreq;
        
        return nameMatch && typeMatch && frequencyMatch;
    });
    
    updateLibraryTable(filteredData);
}

function filterHistoricalTable() {
    const searchTerm = document.getElementById('historical-search')?.value.toLowerCase() || '';
    const fromDate = document.getElementById('historical-from-date')?.value;
    const toDate = document.getElementById('historical-to-date')?.value;
    
    const filteredData = currentHistoricalData.filter(report => {
        const nameMatch = report.name.toLowerCase().includes(searchTerm);
        
        let dateMatch = true;
        if (fromDate || toDate) {
            const reportDate = new Date(report.generated_at);
            if (fromDate) {
                dateMatch = dateMatch && reportDate >= new Date(fromDate);
            }
            if (toDate) {
                dateMatch = dateMatch && reportDate <= new Date(toDate);
            }
        }
        
        return nameMatch && dateMatch;
    });
    
    updateHistoricalTable(filteredData);
}

// Tab Navigation
function setupTabNavigation() {
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            switchTab(tabName);
        });
    });
}

function switchTab(tabName) {
    // Update buttons
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('border-transparent', 'text-mild-gray');
        btn.classList.remove('border-light-brown', 'text-light-brown');
    });
    
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('active');
        activeButton.classList.remove('border-transparent', 'text-mild-gray');
        activeButton.classList.add('border-light-brown', 'text-light-brown');
    }

    // Update content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    const activeContent = document.getElementById(`${tabName}-tab`);
    if (activeContent) {
        activeContent.classList.add('active');
    }

    // Load data for the active tab
    if (tabName === 'library') {
        loadReportLibrary();
    } else if (tabName === 'historical') {
        loadHistoricalReports();
    }
}

// Refresh the currently active tab
function refreshCurrentTab() {
    // Find the currently active tab
    const activeTabButton = document.querySelector('.tab-button.active');
    
    if (activeTabButton) {
        const tabName = activeTabButton.getAttribute('data-tab');
        switchTab(tabName);
    } else {
        // Default to library tab if none is active
        switchTab('library');
    }
}

// Report Library Functions - Only show reports for this vendor
function loadReportLibrary() {
    console.log('Loading report library data for vendor...');
    // Load real data from backend filtered for vendor
    fetch('/vendor-reports/library?vendor_only=true', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Library API response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Library API response:', data);
        console.log('Number of reports found:', data.data ? data.data.length : (data.reports ? data.reports.length : 0));
        if (data.data) {
            currentLibraryData = data.data;
            updateLibraryTable(currentLibraryData);
        } else if (data.success && data.reports) {
            currentLibraryData = data.reports;
            updateLibraryTable(currentLibraryData);
        } else {
            console.error('Failed to load reports:', data.message || 'Unexpected response format');
            // Show fallback data or empty state
            updateLibraryTable([]);
        }
    })
    .catch(error => {
        console.error('Error loading reports:', error);
        // Show fallback data for vendors
        const fallbackData = getMockVendorReports();
        currentLibraryData = fallbackData;
        updateLibraryTable(fallbackData);
    });
}

function updateLibraryTable(reports) {
    const tbody = document.getElementById('library-tbody');
    tbody.innerHTML = '';

    if (reports.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-file-alt text-4xl text-gray-300 mb-2"></i>
                        <p class="text-lg font-medium mb-1">No Report Schedules</p>
                        <p class="text-sm">No reports have been scheduled for your account yet.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    reports.forEach(report => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        // Generate action buttons for vendors (limited actions)
        const actionButtons = generateVendorActionButtons(report);
        
        row.innerHTML = `
            <td class="px-4 py-4">
                <div class="truncate-text">
                    <div class="text-sm font-medium text-gray-900">${report.name || 'Unnamed Report'}</div>
                    <div class="text-sm text-gray-500">${report.description || 'No description'}</div>
                </div>
            </td>
            <td class="px-4 py-4">
                ${getFormatBadge(report.format || 'pdf')}
            </td>
            <td class="px-4 py-4">
                <span class="text-sm text-gray-900">${report.frequency || 'Not set'}</span>
            </td>
            <td class="px-4 py-4">
                <span class="text-sm text-gray-900">${formatDate(report.last_sent || report.updated_at)}</span>
            </td>
            <td class="px-4 py-4">
                ${getStatusBadge(report.status || 'inactive')}
            </td>
            <td class="px-4 py-4">
                <div class="action-buttons">
                    ${actionButtons}
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Generate action buttons specific for vendors (view and download only)
function generateVendorActionButtons(report) {
    let buttons = [];
    
    // Edit button - vendors can edit their own reports (always visible)
    buttons.push(`
        <button class="text-blue-600 hover:text-blue-900 text-sm" data-action="edit" data-report-id="${report.id}" data-report-name="${report.name}" title="Edit Schedule">
            <i class="fas fa-edit"></i>
        </button>
    `);
    
    // Generate Now button (always visible)
    buttons.push(`
        <button class="text-green-600 hover:text-green-900 text-sm" data-action="generate" data-report-id="${report.id}" data-report-name="${report.name}" title="Generate Now">
            <i class="fas fa-cog"></i>
        </button>
    `);
    
    // Status-based buttons (pause/resume) - only this changes based on status
    if (report.status === 'active') {
        buttons.push(`
            <button class="text-yellow-600 hover:text-yellow-900 text-sm" data-action="pause" data-report-id="${report.id}" data-report-name="${report.name}" title="Pause Schedule">
                <i class="fas fa-pause"></i>
            </button>
        `);
    } else if (report.status === 'paused') {
        buttons.push(`
            <button class="text-blue-600 hover:text-blue-900 text-sm" data-action="resume" data-report-id="${report.id}" data-report-name="${report.name}" title="Resume Schedule">
                <i class="fas fa-play"></i>
            </button>
        `);
    } else {
        // For any other status, show resume button as default
        buttons.push(`
            <button class="text-blue-600 hover:text-blue-900 text-sm" data-action="resume" data-report-id="${report.id}" data-report-name="${report.name}" title="Resume Schedule">
                <i class="fas fa-play"></i>
            </button>
        `);
    }
    
    // Delete button - vendors can delete their own report schedules (always visible)
    buttons.push(`
        <button class="text-red-600 hover:text-red-900 text-sm" data-action="delete" data-report-id="${report.id}" data-report-name="${report.name}" title="Delete Schedule">
            <i class="fas fa-trash"></i>
        </button>
    `);
    
    console.log(`Report ${report.id} status: ${report.status}, buttons generated:`, buttons.length);
    
    if (buttons.length === 0) {
        return '<span class="text-sm text-gray-400">No actions available</span>';
    }
    
    return buttons.join(' ');
}

// Historical Reports Functions - Only show reports for this vendor
function loadHistoricalReports() {
    // Load real data from backend filtered for vendor
    fetch('/vendor-reports/historical?vendor_only=true', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Historical API response:', data);
        if (data.data) {
            currentHistoricalData = data.data;
            updateHistoricalTable(currentHistoricalData);
        } else if (data.success && data.reports) {
            currentHistoricalData = data.reports;
            updateHistoricalTable(currentHistoricalData);
        } else {
            console.error('Failed to load historical reports:', data.message || 'Unexpected response format');
            updateHistoricalTable([]);
        }
    })
    .catch(error => {
        console.error('Error loading historical reports:', error);
        // Show fallback data for vendors
        const fallbackData = getMockVendorHistoricalReports();
        currentHistoricalData = fallbackData;
        updateHistoricalTable(fallbackData);
    });
}

function updateHistoricalTable(reports) {
    const tbody = document.getElementById('historical-tbody');
    tbody.innerHTML = '';

    if (reports.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-archive text-4xl text-gray-300 mb-2"></i>
                        <p class="text-lg font-medium mb-1">No Historical Reports</p>
                        <p class="text-sm">No reports have been generated for your account yet.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    reports.forEach(report => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        
        row.innerHTML = `
            <td class="px-4 py-4">
                <div class="text-sm font-medium text-gray-900 truncate-text">${report.name || 'Unnamed Report'}</div>
            </td>
            <td class="px-4 py-4">
                <div class="text-sm text-gray-900">${report.generated_for || 'My Account'}</div>
            </td>
            <td class="px-4 py-4">
                <div class="text-sm text-gray-900">${formatDate(report.generated_at)}</div>
            </td>
            <td class="px-4 py-4">
                ${getFormatBadge(report.format || 'pdf')}
            </td>
            <td class="px-4 py-4">
                <div class="text-sm text-gray-900">${report.size || report.file_size || 'Unknown'}</div>
            </td>
            <td class="px-4 py-4">
                ${getStatusBadge(report.status || 'completed')}
            </td>
            <td class="px-4 py-4">
                <div class="action-buttons">
                    <button class="text-green-600 hover:text-green-900 text-sm" title="View Report" data-action="view" data-report-id="${report.id}" data-report-name="${report.name}">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="text-blue-600 hover:text-blue-900 text-sm" title="Download Report" data-action="download" data-report-id="${report.id}" data-report-name="${report.name}">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

// Ad-hoc Report Functions for Vendors
function setupAdhocForm() {
    const reportTypeSelect = document.getElementById('report-type');
    
    if (reportTypeSelect) {
        reportTypeSelect.addEventListener('change', function() {
            updateDynamicFilters(this.value);
        });
        
        // Trigger initial filter update
        updateDynamicFilters(reportTypeSelect.value);
    }

    // Setup delivery method radio buttons
    const deliveryRadios = document.querySelectorAll('input[name="delivery_method"]');
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', toggleEmailRecipientsSection);
    });

    const adhocForm = document.getElementById('adhoc-form');
    if (adhocForm) {
        adhocForm.addEventListener('submit', function(e) {
            e.preventDefault();
            generateAdhocReport();
        });
    }

    // Setup modal close handlers
    setupAdhocModalHandlers();
}

// Toggle email recipients section (simplified for vendors - they can only send to themselves)
function toggleEmailRecipientsSection() {
    // For vendors, no additional recipients section needed since they can only send to themselves
    // This function exists to prevent errors but doesn't need to do anything
}

function updateDynamicFilters(reportType) {
    const filtersContainer = document.getElementById('filters-container');
    if (!filtersContainer) return;
    
    filtersContainer.innerHTML = '';

    const vendorFilterConfigs = {
        'vendor_purchases': [
            { label: 'Product Category', type: 'select', options: ['All', 'Coffee Beans', 'Equipment', 'Supplies'] },
            { label: 'Supplier', type: 'select', options: ['All', 'Primary Suppliers', 'Secondary Suppliers'] }
        ],
        'vendor_orders': [
            { label: 'Order Status', type: 'select', options: ['All', 'Pending', 'Confirmed', 'Shipped', 'Delivered'] },
            { label: 'Priority', type: 'select', options: ['All', 'High', 'Medium', 'Low'] }
        ],
        'vendor_deliveries': [
            { label: 'Delivery Status', type: 'select', options: ['All', 'Scheduled', 'In Transit', 'Delivered'] },
            { label: 'Delivery Type', type: 'select', options: ['All', 'Standard', 'Express', 'Bulk'] }
        ],
        'vendor_payments': [
            { label: 'Payment Status', type: 'select', options: ['All', 'Pending', 'Paid', 'Overdue'] },
            { label: 'Payment Method', type: 'select', options: ['All', 'Bank Transfer', 'Credit Card', 'Check'] }
        ],
        'vendor_inventory': [
            { label: 'Product Category', type: 'select', options: ['All', 'Coffee Beans', 'Equipment', 'Supplies'] },
            { label: 'Stock Status', type: 'select', options: ['All', 'In Stock', 'Low Stock', 'Out of Stock'] }
        ]
    };

    const filters = vendorFilterConfigs[reportType] || [];
    
    filters.forEach(filter => {
        const filterDiv = document.createElement('div');
        filterDiv.innerHTML = `
            <label class="block text-sm font-medium text-gray-700 mb-2">${filter.label}</label>
            <select name="${filter.label.toLowerCase().replace(' ', '_')}" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-light-brown focus:border-light-brown">
                ${filter.options.map(option => `<option value="${option.toLowerCase()}">${option}</option>`).join('')}
            </select>
        `;
        filtersContainer.appendChild(filterDiv);
    });
}

function generateAdhocReport() {
    // Get form data
    const reportType = document.getElementById('report-type').value;
    const format = document.getElementById('output-format').value;
    const fromDate = document.getElementById('from-date').value;
    const toDate = document.getElementById('to-date').value;
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    
    // Validate required fields
    if (!reportType) {
        showNotification('Please select a report type.', 'warning');
        return;
    }
    
    if (!fromDate || !toDate) {
        showNotification('Please select both from and to dates.', 'warning');
        return;
    }
    
    // Show generation modal
    openAdhocGenerationModal();
    
    // Start generation process
    simulateReportGeneration(reportType, format, deliveryMethod);
}

async function simulateReportGeneration(reportType, format, deliveryMethod) {
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    
    try {
        // Prepare form data
        const formData = new FormData();
        formData.append('report_type', reportType);
        formData.append('format', format);
        formData.append('from_date', document.getElementById('from-date').value);
        formData.append('to_date', document.getElementById('to-date').value);
        formData.append('delivery_method', deliveryMethod);
        
        // For suppliers, they can only send to themselves
        formData.append('recipients[]', 'supplier'); // Will be handled by backend to map to current user
        
        // Add CSRF token
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Step 1: Initialize
        progressText.textContent = 'Initializing report generation...';
        progressBar.style.width = '10%';
        await new Promise(resolve => setTimeout(resolve, 500));
        
        // Step 2: Send request to backend
        progressText.textContent = 'Processing request...';
        progressBar.style.width = '30%';
        
        const response = await fetch('/vendor-reports/adhoc', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        progressText.textContent = 'Generating report content...';
        progressBar.style.width = '70%';
        await new Promise(resolve => setTimeout(resolve, 1000));
        
        const result = await response.json();
        
        if (result.success) {
            progressText.textContent = 'Finalizing report...';
            progressBar.style.width = '100%';
            await new Promise(resolve => setTimeout(resolve, 500));
            
            // Store the report ID for download/view actions
            window.currentReportId = result.report_id;
            
            // Show success
            showGenerationSuccess(reportType, format, deliveryMethod);
        } else {
            throw new Error(result.message || 'Failed to generate report');
        }
        
    } catch (error) {
        console.error('Report generation error:', error);
        showGenerationError(error.message || 'Failed to generate report. Please try again.');
    }
}

function setupAdhocModalHandlers() {
    // Close modal
    const closeBtn = document.getElementById('close-adhoc-modal');
    if (closeBtn) {
        closeBtn.addEventListener('click', closeAdhocModal);
    }

    // Download button
    const downloadBtn = document.getElementById('download-report-btn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', downloadGeneratedReport);
    }

    // View button
    const viewBtn = document.getElementById('view-report-btn');
    if (viewBtn) {
        viewBtn.addEventListener('click', viewGeneratedReport);
    }

    // Retry button
    const retryBtn = document.getElementById('retry-generation-btn');
    if (retryBtn) {
        retryBtn.addEventListener('click', retryReportGeneration);
    }
}

// Missing modal handler functions
function downloadGeneratedReport() {
    if (!window.currentReportId) {
        showNotification('No report available for download', 'error');
        return;
    }
    
    // Create a temporary link to trigger download
    const downloadUrl = `/vendor-reports/${window.currentReportId}/download`;
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = ''; // Let server determine filename
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    showNotification('Download started...', 'info');
    closeAdhocModal();
}

function viewGeneratedReport() {
    console.log('viewGeneratedReport called, currentReportId:', window.currentReportId);
    
    if (!window.currentReportId) {
        showNotification('No report available for viewing', 'error');
        return;
    }
    
    // Open report in new tab
    const viewUrl = `/vendor-reports/${window.currentReportId}/view`;
    console.log('Opening view URL:', viewUrl);
    window.open(viewUrl, '_blank');
    
    showNotification('Opening report in new window...', 'info');
}

function retryReportGeneration() {
    // Reset the modal and try generation again
    const reportType = document.getElementById('report-type').value;
    const format = document.getElementById('output-format').value;
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    
    if (reportType) {
        openAdhocGenerationModal();
        simulateReportGeneration(reportType, format, deliveryMethod);
    }
}

function openAdhocGenerationModal() {
    const modal = document.getElementById('adhocGenerationModal');
    const progressSection = document.getElementById('generation-progress');
    const successSection = document.getElementById('generation-success');
    const errorSection = document.getElementById('generation-error');
    
    // Reset modal state
    progressSection.classList.remove('hidden');
    successSection.classList.add('hidden');
    errorSection.classList.add('hidden');
    
    // Reset progress
    document.getElementById('progress-bar').style.width = '0%';
    document.getElementById('progress-text').textContent = 'Initializing...';
    
    modal.classList.remove('hidden');
}

function closeAdhocModal() {
    document.getElementById('adhocGenerationModal').classList.add('hidden');
}

function showGenerationSuccess(reportType, format, deliveryMethod) {
    const progressSection = document.getElementById('generation-progress');
    const successSection = document.getElementById('generation-success');
    const emailNotice = document.getElementById('email-sent-notice');
    
    // Hide progress, show success
    progressSection.classList.add('hidden');
    successSection.classList.remove('hidden');
    
    // Update result details
    document.getElementById('result-type').textContent = formatReportTypeName(reportType);
    document.getElementById('result-format').textContent = format.toUpperCase();
    document.getElementById('result-size').textContent = generateRandomFileSize();
    document.getElementById('result-time').textContent = 'Just now';
    
    // Show email notice if applicable
    if (deliveryMethod === 'email') {
        emailNotice.classList.remove('hidden');
    } else {
        emailNotice.classList.add('hidden');
    }
    
    // Add to historical reports
    addToHistoricalReports(reportType, format, deliveryMethod);
}

function addToHistoricalReports(reportType, format, deliveryMethod) {
    // Refresh historical reports if we're on that tab
    const activeTab = document.querySelector('.tab-button.active');
    if (activeTab && activeTab.dataset.tab === 'historical') {
        loadHistoricalReports();
    }
}

function showGenerationError(message) {
    const progressSection = document.getElementById('generation-progress');
    const errorSection = document.getElementById('generation-error');
    
    progressSection.classList.add('hidden');
    errorSection.classList.remove('hidden');
    
    document.getElementById('error-message').textContent = message;
}

// Action Button Event Handlers
function setupActionButtonHandlers() {
    // Use event delegation for dynamically created buttons
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button[data-action]');
        if (!button) return;

        e.preventDefault();
        const action = button.dataset.action;
        const reportId = button.dataset.reportId;
        const reportName = button.dataset.reportName;

        console.log('Action button clicked:', action, 'for report:', reportId);

        switch (action) {
            case 'edit':
                handleEditReport(reportId);
                break;
            case 'generate':
                handleGenerateReport(reportId, reportName);
                break;
            case 'delete':
                handleDeleteReport(reportId, reportName);
                break;
            case 'download':
                handleDownloadReport(reportId, reportName);
                break;
            case 'view':
                handleViewReport(reportId, reportName);
                break;
            case 'pause':
                handlePauseReport(reportId, reportName);
                break;
            case 'resume':
                handleResumeReport(reportId, reportName);
                break;
            default:
                console.warn('Unknown action:', action);
        }
    });
}

// Action Handler Functions
async function handleGenerateReport(reportId, reportName) {
    if (!confirm(`Generate report "${reportName}" now?`)) return;

    try {
        const response = await fetch(`/vendor-reports/${reportId}/generate`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        
        if (data.success) {
            showNotification(`"${reportName}" generated successfully!`, 'success');
            refreshCurrentTab();
            updateStatsCards();
        } else {
            showNotification('Failed to generate report: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Error generating report:', error);
        showNotification('Failed to generate report', 'error');
    }
}

async function handleDownloadReport(reportId, reportName) {
    try {
        // Create a temporary link to trigger download
        const downloadUrl = `/vendor-reports/${reportId}/download`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = ''; // Let server determine filename
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification(`Downloading "${reportName}"...`, 'info');
    } catch (error) {
        console.error('Error downloading report:', error);
        showNotification('Failed to download report', 'error');
    }
}

async function handleViewReport(reportId, reportName) {
    try {
        console.log('Vendor handleViewReport called with reportId:', reportId, 'reportName:', reportName);
        
        // Open report in new tab
        const viewUrl = `/vendor-reports/${reportId}/view`;
        console.log('Opening URL:', viewUrl);
        
        const newWindow = window.open(viewUrl, '_blank');
        
        // Check if popup was blocked
        if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
            console.warn('Popup was blocked, redirecting in current tab');
            showNotification('Popup blocked. Opening report in current tab...', 'warning');
            window.location.href = viewUrl;
        } else {
            console.log('Report opened in new tab successfully');
            
            // Add a listener to check what URL the popup actually loads
            setTimeout(() => {
                try {
                    console.log('Popup current URL:', newWindow.location.href);
                } catch (e) {
                    console.log('Cannot access popup URL (cross-origin):', e.message);
                }
            }, 2000);
            
            showNotification(`Opening "${reportName}" in new window...`, 'info');
        }
    } catch (error) {
        console.error('Error viewing report:', error);
        showNotification('Failed to open report', 'error');
    }
}

// Additional Action Handler Functions for Suppliers
async function handleEditReport(reportId) {
    try {
        showNotification('Loading report data...', 'info');
        
        // Try to fetch the report data from the backend
        try {
            const response = await fetch(`/vendor-reports/${reportId}/edit`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            
            if (result.success) {
                // Open edit modal with backend data
                openEditModal(result.data.report, result.data.templates, result.data.recipients);
            } else {
                throw new Error(result.message || 'Backend request failed');
            }
        } catch (backendError) {
            console.log('Using mock data for demo:', backendError.message);
            
            // Find the report in current library data
            const report = currentLibraryData.find(r => r.id == reportId);
            
            if (!report) {
                showNotification('Report not found', 'error');
                return;
            }
            
            // Create mock report data for editing
            const mockReport = {
                id: report.id,
                name: report.name,
                description: report.description || '',
                type: 'supplier_performance',
                format: report.format || 'pdf',
                frequency: report.frequency ? report.frequency.toLowerCase() : 'weekly',
                recipients: [1], // Default to vendor as recipient
                status: report.status
            };
            
            // Get mock data for the dropdowns
            const mockTemplates = [
                { id: 'supplier_performance', name: 'Supplier Performance Report' },
                { id: 'inventory_status', name: 'Inventory Status Report' },
                { id: 'delivery_tracking', name: 'Delivery Tracking Report' }
            ];
            
            // Try to fetch real recipients from API, fall back to mock if failed
            try {
                const recipientsResponse = await fetch('/vendor-reports/recipients', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const recipientsData = await recipientsResponse.json();
                
                if (recipientsData.users && recipientsData.users.length > 0) {
                    // Use real recipients from API
                    openEditModal(mockReport, mockTemplates, recipientsData.users);
                } else {
                    throw new Error('No recipients data received');
                }
            } catch (recipientsError) {
                console.log('Failed to fetch recipients, using meta tags:', recipientsError.message);
                
                // Get current user information from meta tags as fallback
                const currentUserId = document.querySelector('meta[name="user-id"]')?.getAttribute('content') || '1';
                const currentUserName = document.querySelector('meta[name="user-name"]')?.getAttribute('content') || 'Current User';
                const currentUserEmail = document.querySelector('meta[name="user-email"]')?.getAttribute('content') || 'user@example.com';
                
                const mockRecipients = [
                    { id: parseInt(currentUserId), name: currentUserName, email: currentUserEmail }
                ];
                
                openEditModal(mockReport, mockTemplates, mockRecipients);
            }
        }
    } catch (error) {
        console.error('Error editing report:', error);
        showNotification('Failed to edit report', 'error');
    }
}

async function handlePauseReport(reportId, reportName) {
    if (!confirm(`Pause the schedule for "${reportName}"?`)) return;
    
    try {
        const response = await fetch(`/vendor-reports/${reportId}/pause`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showNotification(`"${reportName}" has been paused`, 'success');
                
                // Update the report status in current data
                const reportIndex = currentLibraryData.findIndex(r => r.id == reportId);
                if (reportIndex !== -1) {
                    currentLibraryData[reportIndex].status = 'paused';
                }
                
                refreshCurrentTab();
                updateStatsCards();
            } else {
                showNotification('Failed to pause report: ' + (data.message || 'Unknown error'), 'error');
            }
        } else {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
    } catch (error) {
        console.error('Error pausing report:', error);
        showNotification('Failed to pause report', 'error');
    }
}

async function handleResumeReport(reportId, reportName) {
    if (!confirm(`Resume the schedule for "${reportName}"?`)) return;
    
    try {
        const response = await fetch(`/vendor-reports/${reportId}/resume`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showNotification(`"${reportName}" has been resumed`, 'success');
                
                // Update the report status in current data
                const reportIndex = currentLibraryData.findIndex(r => r.id == reportId);
                if (reportIndex !== -1) {
                    currentLibraryData[reportIndex].status = 'active';
                }
                
                refreshCurrentTab();
                updateStatsCards();
            } else {
                showNotification('Failed to resume report: ' + (data.message || 'Unknown error'), 'error');
            }
        } else {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
    } catch (error) {
        console.error('Error resuming report:', error);
        showNotification('Failed to resume report', 'error');
    }
}

async function handleDeleteReport(reportId, reportName) {
    if (!confirm(`Are you sure you want to delete "${reportName}"? This action cannot be undone.`)) return;
    
    try {
        const response = await fetch(`/vendor-reports/${reportId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                showNotification(`"${reportName}" has been deleted`, 'success');
                refreshCurrentTab();
                updateStatsCards();
            } else {
                showNotification('Failed to delete report: ' + (data.message || 'Unknown error'), 'error');
            }
        } else {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
    } catch (error) {
        console.error('Error deleting report:', error);
        showNotification('Failed to delete report', 'error');
    }
}

// Setup Create Schedule Modal
function setupCreateScheduleModal() {
    console.log('Setting up create schedule modal');
    // Create Schedule button
    const createScheduleBtn = document.getElementById('create-schedule-btn');
    console.log('Create schedule button found:', !!createScheduleBtn);
    if (createScheduleBtn) {
        createScheduleBtn.addEventListener('click', function(e) {
            console.log('Create schedule button clicked');
            e.preventDefault();
            openCreateScheduleModal();
        });
        console.log('Event listener added to create schedule button');
    } else {
        console.error('Create schedule button not found');
    }

    // Generate Ad-hoc button
    const generateAdhocBtn = document.getElementById('generate-adhoc-btn');
    if (generateAdhocBtn) {
        generateAdhocBtn.addEventListener('click', openAdhocModal);
    }

    // Template selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.template-option').forEach(opt => {
                opt.classList.remove('border-light-brown', 'bg-orange-50');
                opt.classList.add('border-gray-200');
            });
            this.classList.add('border-light-brown', 'bg-orange-50');
            this.classList.remove('border-gray-200');
            selectedTemplate = this.getAttribute('data-template');
        });
    });

    // Format selection
    document.querySelectorAll('.format-option').forEach(option => {
        option.addEventListener('click', function() {
            console.log('Format option clicked:', this.getAttribute('data-format'));
            document.querySelectorAll('.format-option').forEach(opt => {
                opt.classList.remove('border-light-brown', 'bg-orange-50');
                opt.classList.add('border-gray-200');
            });
            this.classList.add('border-light-brown', 'bg-orange-50');
            this.classList.remove('border-gray-200');
            selectedFormat = this.getAttribute('data-format');
            console.log('Format selected and stored:', selectedFormat);
        });
    });

    // Wizard navigation
    const nextBtn = document.getElementById('next-btn');
    const prevBtn = document.getElementById('prev-btn');
    const saveBtn = document.getElementById('save-btn');

    if (nextBtn) {
        nextBtn.addEventListener('click', nextStep);
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', previousStep);
    }
    if (saveBtn) {
        console.log('Setting up save button event listener');
        saveBtn.addEventListener('click', function(e) {
            console.log('Save button clicked');
            e.preventDefault();
            saveVendorReportSchedule();
        });
    } else {
        console.error('Save button not found in DOM');
    }
}

function openCreateScheduleModal() {
    console.log('Opening create schedule modal');
    const modal = document.getElementById('createReportModal');
    console.log('Modal element found:', !!modal);
    if (modal) {
        modal.classList.remove('hidden');
        console.log('Modal should now be visible');
        
        // Reset form state
        selectedTemplate = '';
        selectedFormat = '';
        currentStep = 1;
        
        console.log('Reset selections - template:', selectedTemplate, 'format:', selectedFormat);
        
        // Clear any previous selections
        document.querySelectorAll('.template-option').forEach(option => {
            option.classList.remove('border-light-brown', 'bg-orange-50');
            option.classList.add('border-gray-200');
        });
        document.querySelectorAll('.format-option').forEach(option => {
            option.classList.remove('border-light-brown', 'bg-orange-50');
            option.classList.add('border-gray-200');
        });
        
        // Reset form fields
        const frequencySelect = document.getElementById('frequency');
        const timeInput = document.getElementById('schedule-time');
        const daySelect = document.getElementById('schedule-day');
        
        if (frequencySelect) frequencySelect.value = '';
        if (timeInput) timeInput.value = '08:00';
        if (daySelect) daySelect.value = 'monday';
        
        // Make sure step 1 is visible and active
        document.querySelectorAll('.wizard-step').forEach((step, index) => {
            step.classList.remove('active');
            step.style.display = 'none';
            if (index === 0) { // First step
                step.classList.add('active');
                step.style.display = 'block';
            }
        });
        
        // Update wizard navigation
        updateWizardStep();
        
        console.log('Modal initialization completed');
    } else {
        console.error('Modal element not found');
    }
}

function closeCreateScheduleModal() {
    document.getElementById('createReportModal').classList.add('hidden');
    currentStep = 1;
    selectedTemplate = '';
    selectedFormat = '';
    
    // Reset save button state
    resetSaveButton();
}

function nextStep() {
    console.log('nextStep() called, current step:', currentStep);
    console.log('Current selections:', {
        template: selectedTemplate,
        format: selectedFormat,
        frequency: document.getElementById('frequency')?.value
    });
    
    // Don't allow moving beyond step 4 (the review step)
    if (currentStep >= 4) {
        console.log('Already on final step, cannot proceed further');
        return;
    }
    
    if (validateCurrentStep()) {
        currentStep++;
        console.log('Moving to step:', currentStep);
        updateWizardStep();
        if (currentStep === 4) { // Review step
            console.log('Reached review step, updating review data');
            updateReviewData();
        }
    } else {
        console.log('Validation failed for step:', currentStep);
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardStep();
    }
}

function updateWizardStep() {
    console.log('Updating wizard step to:', currentStep);
    
    // Update step indicators - need to target the inner div with the circle
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const circle = indicator.querySelector('div:first-child');
        if (circle) {
            if (index + 1 <= currentStep) {
                circle.classList.add('bg-soft-brown', 'text-white');
                circle.classList.remove('bg-soft-gray', 'text-warm-gray');
            } else {
                circle.classList.remove('bg-soft-brown', 'text-white');
                circle.classList.add('bg-soft-gray', 'text-warm-gray');
            }
        }
    });

    // Update content
    document.querySelectorAll('.wizard-step').forEach(step => {
        step.classList.remove('active');
        step.style.display = 'none'; // Force hide all steps
    });
    
    const activeStep = document.querySelector(`[data-step="${currentStep}"]`);
    if (activeStep) {
        activeStep.classList.add('active');
        activeStep.style.display = 'block'; // Force show active step
        console.log(`Step ${currentStep} is now active`);
    } else {
        console.error(`Step ${currentStep} not found in DOM`);
    }

    // Update buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const saveBtn = document.getElementById('save-btn');

    if (prevBtn) {
        if (currentStep > 1) {
            prevBtn.classList.remove('hidden');
        } else {
            prevBtn.classList.add('hidden');
        }
    }

    if (nextBtn && saveBtn) {
        console.log('Button update - current step:', currentStep);
        console.log('Next button exists:', !!nextBtn);
        console.log('Save button exists:', !!saveBtn);
        
        if (currentStep === 4) { // Last step (Review)
            console.log('On review step - hiding next, showing save');
            nextBtn.classList.add('hidden');
            nextBtn.style.display = 'none'; // Force hide next button
            saveBtn.classList.remove('hidden');
            saveBtn.style.display = 'inline-block'; // Force show save button
        } else {
            console.log('Not on review step - showing next, hiding save');
            nextBtn.classList.remove('hidden');
            nextBtn.style.display = 'inline-block'; // Force show next button
            saveBtn.classList.add('hidden');
            saveBtn.style.display = 'none'; // Force hide save button
        }
    } else {
        console.error('Button elements not found:', { nextBtn: !!nextBtn, saveBtn: !!saveBtn });
    }
}

function validateCurrentStep() {
    console.log('Validating step:', currentStep);
    switch (currentStep) {
        case 1:
            if (!selectedTemplate) {
                showNotification('Please select a report template', 'error');
                return false;
            }
            console.log('Step 1 validated - template:', selectedTemplate);
            break;
        case 2:
            const frequency = document.getElementById('frequency')?.value;
            if (!frequency) {
                showNotification('Please select a frequency', 'error');
                return false;
            }
            console.log('Step 2 validated - frequency:', frequency);
            break;
        case 3:
            if (!selectedFormat) {
                showNotification('Please select a format', 'error');
                return false;
            }
            console.log('Step 3 validated - format:', selectedFormat);
            break;
        case 4:
            // Step 4 is the review step - no next step allowed
            console.log('Step 4 is the final step - cannot proceed further');
            return false;
    }
    return true;
}

function updateReviewData() {
    console.log('Updating review data');
    console.log('Selected template:', selectedTemplate);
    console.log('Selected format:', selectedFormat);
    
    const elements = {
        'review-template': selectedTemplate || 'Not selected',
        'review-format': selectedFormat ? selectedFormat.toUpperCase() : 'Not selected',
        'review-frequency': document.getElementById('frequency')?.value || 'Not selected',
        'review-schedule': getScheduleDescription()
    };

    console.log('Review elements to update:', elements);

    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        console.log(`Updating ${id} with value:`, value, 'Element found:', !!element);
        if (element) {
            element.textContent = value;
        } else {
            console.error(`Element with id ${id} not found in DOM`);
        }
    });
    
    // The recipient is already set in the blade template, no need to update it
    console.log('Review data update completed');
}

function getScheduleDescription() {
    const frequency = document.getElementById('frequency')?.value;
    const time = document.getElementById('schedule-time')?.value;
    const day = document.getElementById('schedule-day')?.value;
    
    if (!frequency) {
        return 'Not configured';
    }
    
    let description = frequency.charAt(0).toUpperCase() + frequency.slice(1);
    
    if (frequency === 'weekly' && day) {
        const dayNames = {
            'monday': 'Monday',
            'tuesday': 'Tuesday', 
            'wednesday': 'Wednesday',
            'thursday': 'Thursday',
            'friday': 'Friday',
            'saturday': 'Saturday',
            'sunday': 'Sunday'
        };
        description += ` on ${dayNames[day] || day}`;
    } else if (frequency === 'monthly' && day) {
        if (day === 'last') {
            description += ' on the last day';
        } else if (day === '1') {
            description += ' on the 1st';
        } else if (day === '15') {
            description += ' on the 15th';
        } else {
            description += ` on ${day}`;
        }
    }
    
    if (time) {
        description += ` at ${time}`;
    }
    
    return description;
}

function saveVendorReportSchedule() {
    console.log('Save supplier report schedule called');
    
    // Prevent multiple submissions
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn && saveBtn.disabled) {
        console.log('Save already in progress, ignoring duplicate request');
        return;
    }
    
    // Disable save button to prevent duplicates
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
    }
    
    const frequency = document.getElementById('frequency')?.value;
    const scheduleTime = document.getElementById('schedule-time')?.value;
    const scheduleDay = document.getElementById('schedule-day')?.value;

    console.log('Form data being prepared for submission:', {
        template: selectedTemplate,
        frequency: frequency,
        format: selectedFormat,
        scheduleTime: scheduleTime,
        scheduleDay: scheduleDay
    });

    // Double-check the format value right before sending
    console.log('Final format value before sending:', selectedFormat);
    console.log('Format type:', typeof selectedFormat);

    // Validate all required fields
    if (!selectedTemplate) {
        showNotification('Please select a report template', 'error');
        resetSaveButton();
        return;
    }
    
    if (!frequency) {
        showNotification('Please select a frequency', 'error');
        resetSaveButton();
        return;
    }
    
    if (!selectedFormat) {
        showNotification('Please select a report format', 'error');
        resetSaveButton();
        return;
    }

    const data = {
        name: selectedTemplate,
        template: selectedTemplate,
        recipients: [document.querySelector('meta[name="user-id"]')?.getAttribute('content') || '1'], // Current supplier user
        frequency: frequency,
        format: selectedFormat,
        schedule_time: scheduleTime,
        schedule_day: scheduleDay,
        supplier_only: true
    };

    console.log('Final data object being sent to server:', JSON.stringify(data, null, 2));

    fetch('/vendor-reports', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification('Report schedule created successfully!', 'success');
            closeCreateScheduleModal();
            
            // Refresh the current tab data
            refreshCurrentTab();
            
            // Update stats cards
            updateStatsCards();
            
            // Force reload the library data to make sure new schedule appears
            setTimeout(() => {
                loadReportLibrary();
            }, 1000);
            
        } else {
            showNotification('Failed to create report schedule: ' + (data.message || 'Unknown error'), 'error');
            resetSaveButton();
        }
    })
    .catch(error => {
        console.error('Error creating report schedule:', error);
        showNotification('Failed to create report schedule. Please try again.', 'error');
        resetSaveButton();
    });
}

// Helper function to reset save button state
function resetSaveButton() {
    const saveBtn = document.getElementById('save-btn');
    if (saveBtn) {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Create Schedule';
    }
}

// Ad-hoc Modal Functions
function openAdhocModal() {
    document.getElementById('adhocGenerationModal').classList.remove('hidden');
}

// Unified Stats Card Management Functions
async function updateStatsCards() {
    try {
        console.log('Updating supplier stats cards...');
        const response = await fetch('/vendor-reports/stats?vendor_only=true', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Supplier stats API response:', data);
        
        if (data.success && data.data) {
            console.log('Updating supplier stats cards with:', data.data);
            updateStatsCard('active-reports-card', data.data.activeReports || 0);
            updateStatsCard('generated-today-card', data.data.generatedToday || 0);
            updateStatsCard('total-reports-card', data.data.totalReports || 0);
            updateStatsCard('latest-report-card', data.data.lastGenerated || 'None');
        } else {
            console.warn('Supplier stats update failed:', data.message);
        }
    } catch (error) {
        console.error('Error updating supplier stats cards:', error);
    }
}

// Generic function to update any stats card with animation
function updateStatsCard(cardId, newValue) {
    const card = document.getElementById(cardId);
    
    if (card) {
        const valueElement = card.querySelector('p[data-value]');
        
        if (valueElement) {
            console.log(`Updating supplier ${cardId} from ${valueElement.getAttribute('data-value')} to ${newValue}`);
            
            valueElement.textContent = newValue;
            valueElement.setAttribute('data-value', newValue);
            
            // Add update animation
            card.classList.add('transform', 'scale-105', 'transition-transform', 'duration-200');
            setTimeout(() => {
                card.classList.remove('transform', 'scale-105');
            }, 200);
        } else {
            console.warn(`No p[data-value] element found in supplier card ${cardId}`);
        }
    } else {
        console.warn(`Supplier card ${cardId} not found`);
    }
}

// Notification system
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg max-w-sm transition-all duration-300 transform translate-x-full`;
    
    // Set colors based on type
    let bgColor, textColor, iconClass;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            textColor = 'text-white';
            iconClass = 'fas fa-check-circle';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            textColor = 'text-white';
            iconClass = 'fas fa-exclamation-circle';
            break;
        case 'warning':
            bgColor = 'bg-yellow-500';
            textColor = 'text-white';
            iconClass = 'fas fa-exclamation-triangle';
            break;
        default:
            bgColor = 'bg-blue-500';
            textColor = 'text-white';
            iconClass = 'fas fa-info-circle';
    }
    
    notification.classList.add(bgColor, textColor);
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="${iconClass} mr-2"></i>
            <span>${message}</span>
            <button class="ml-4 text-white hover:text-gray-200" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Utility Functions
function getFormatBadge(format) {
    const formatClasses = {
        'pdf': 'bg-red-100 text-red-800',
        'excel': 'bg-green-100 text-green-800',
        'csv': 'bg-blue-100 text-blue-800',
        'dashboard': 'bg-purple-100 text-purple-800'
    };

    const icons = {
        'pdf': 'fas fa-file-pdf',
        'excel': 'fas fa-file-excel',
        'csv': 'fas fa-file-csv',
        'dashboard': 'fas fa-chart-bar'
    };

    const className = formatClasses[format] || 'bg-gray-100 text-gray-800';
    const icon = icons[format] || 'fas fa-file';
    
    return `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${className}">
        <i class="${icon} mr-1"></i> ${format.toUpperCase()}
    </span>`;
}

function getStatusBadge(status) {
    const statusClasses = {
        'active': 'bg-green-100 text-green-800',
        'paused': 'bg-gray-100 text-gray-800',
        'failed': 'bg-red-100 text-red-800',
        'processing': 'bg-yellow-100 text-yellow-800',
        'completed': 'bg-blue-100 text-blue-800',
        'success': 'bg-green-100 text-green-800'
    };

    const className = statusClasses[status] || 'bg-gray-100 text-gray-800';
    
    return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${className}">
        ${status.charAt(0).toUpperCase() + status.slice(1)}
    </span>`;
}

function getMockVendorReports() {
    return [
        {
            id: 1,
            name: 'Vendor Purchases Report',
            type: 'purchases',
            format: 'pdf',
            frequency: 'Daily',
            last_generated: '2024-12-20',
            status: 'active',
            recipients: 'Current User',
            last_sent: '2024-12-20 08:00:00'
        },
        {
            id: 2,
            name: 'Vendor Orders Report',
            type: 'orders',
            format: 'excel',
            frequency: 'Weekly',
            last_generated: '2024-12-18',
            status: 'active',
            recipients: 'Current User',
            last_sent: '2024-12-18 09:00:00'
        },
        {
            id: 3,
            name: 'Vendor Payments Report',
            type: 'payments',
            format: 'pdf',
            frequency: 'Monthly',
            last_generated: '2024-12-01',
            status: 'paused',
            recipients: 'Current User',
            last_sent: '2024-12-01 10:00:00'
        }
    ];
}

// Mock data for vendor historical reports
function getMockVendorHistoricalReports() {
    return [
        {
            id: 1,
            name: 'Monthly Purchases Report',
            generated_for: 'My Account',
            generated_at: '2024-12-01',
            format: 'pdf',
            file_size: '1.2 MB',
            status: 'completed'
        },
        {
            id: 2,
            name: 'Order Status Report',
            generated_for: 'My Account',
            generated_at: '2024-11-15',
            format: 'excel',
            file_size: '850 KB',
            status: 'completed'
        },
        {
            id: 3,
            name: 'Payment History Report',
            generated_for: 'My Account',
            generated_at: '2024-11-01',
            format: 'csv',
            file_size: '650 KB',
            status: 'completed'
        }
    ];
}

function formatDate(dateString) {
    if (!dateString) return 'Never';
    
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return 'Invalid Date';
        
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        return 'Invalid Date';
    }
}

function generateRandomFileSize() {
    const sizes = ['1.2 MB', '2.3 MB', '856 KB', '3.1 MB', '1.8 MB', '4.2 MB'];
    return sizes[Math.floor(Math.random() * sizes.length)];
}

function formatReportTypeName(reportType) {
    const typeNames = {
        'supplier_inventory': 'Supplier Inventory',
        'supplier_orders': 'Supplier Orders',
        'supplier_quality': 'Supplier Quality',
        'supplier_deliveries': 'Supplier Deliveries'
    };
    return typeNames[reportType] || reportType;
}

// Edit Modal Functions
function openEditModal(report, templates, recipients) {
    // Create edit modal if it doesn't exist
    let editModal = document.getElementById('supplier-edit-report-modal');
    if (!editModal) {
        createSupplierEditModal();
        editModal = document.getElementById('supplier-edit-report-modal');
    }
    
    // Populate form with report data
    populateSupplierEditForm(report, templates, recipients);
    
    // Show modal
    editModal.classList.remove('hidden');
}

function createSupplierEditModal() {
    const modalHTML = `
        <div id="supplier-edit-report-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                    <div class="flex items-center justify-between p-6 border-b">
                        <h3 class="text-lg font-semibold text-dashboard-light">Edit Report</h3>
                        <button id="close-supplier-edit-modal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="supplier-edit-report-form" class="p-6">
                        <input type="hidden" id="supplier-edit-report-id" name="report_id">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Report Name</label>
                                <input type="text" id="supplier-edit-report-name" name="name" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="supplier-edit-report-description" name="description" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select id="supplier-edit-report-type" name="type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                        <option value="">Select Type</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                                    <select id="supplier-edit-report-format" name="format" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                        <option value="">Select Format</option>
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                        <option value="csv">CSV</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                                <select id="supplier-edit-report-frequency" name="frequency" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                    <option value="">Select Frequency</option>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                                <div id="supplier-edit-recipients-container" class="space-y-2">
                                    <!-- Recipients will be populated here -->
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                            <button type="button" id="cancel-supplier-edit" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Add event listeners
    document.getElementById('close-supplier-edit-modal').addEventListener('click', closeSupplierEditModal);
    document.getElementById('cancel-supplier-edit').addEventListener('click', closeSupplierEditModal);
    document.getElementById('supplier-edit-report-form').addEventListener('submit', handleSupplierEditSubmit);
}

function populateSupplierEditForm(report, templates, recipients) {
    // Fill basic fields
    document.getElementById('supplier-edit-report-id').value = report.id;
    document.getElementById('supplier-edit-report-name').value = report.name;
    document.getElementById('supplier-edit-report-description').value = report.description || '';
    
    // Populate templates dropdown
    const typeSelect = document.getElementById('supplier-edit-report-type');
    typeSelect.innerHTML = '<option value="">Select Type</option>';
    templates.forEach(template => {
        const option = document.createElement('option');
        option.value = template.id;
        option.textContent = template.name;
        if (template.id === report.type) {
            option.selected = true;
        }
        typeSelect.appendChild(option);
    });
    
    // Set format and frequency
    document.getElementById('supplier-edit-report-format').value = report.format;
    document.getElementById('supplier-edit-report-frequency').value = report.frequency;
    
    // Populate recipients
    const recipientsContainer = document.getElementById('supplier-edit-recipients-container');
    recipientsContainer.innerHTML = '';
    
    recipients.forEach(recipient => {
        const isSelected = report.recipients && report.recipients.includes(recipient.id);
        const recipientHTML = `
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="recipients[]" value="${recipient.id}" ${isSelected ? 'checked' : ''} 
                       class="rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <span class="text-sm text-gray-700">${recipient.name} (${recipient.email})</span>
            </label>
        `;
        recipientsContainer.insertAdjacentHTML('beforeend', recipientHTML);
    });
}

function closeSupplierEditModal() {
    const modal = document.getElementById('supplier-edit-report-modal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

async function handleSupplierEditSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const reportId = formData.get('report_id');
    
    // Get selected recipients
    const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
        .map(checkbox => parseInt(checkbox.value));
    
    const reportData = {
        name: formData.get('name'),
        description: formData.get('description'),
        type: formData.get('type'),
        format: formData.get('format'),
        frequency: formData.get('frequency'),
        recipients: selectedRecipients
    };
    
    try {
        showNotification('Updating report...', 'info');
        
        const response = await fetch(`/reports/${reportId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(reportData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Report updated successfully', 'success');
            closeSupplierEditModal();
            loadReportLibrary(); // Refresh the library table
            updateStatsCards(); // Update stats
        } else {
            showNotification(result.message || 'Failed to update report', 'error');
        }
    } catch (error) {
        console.error('Error updating report:', error);
        showNotification('Error updating report', 'error');
    }
}
