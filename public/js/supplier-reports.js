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
        console.log('Supplier reports JS loaded');
        
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
        console.log('All setup functions completed successfully');
    } catch (error) {
        console.error('Error during initialization:', error);
    }
});

// Make functions globally accessible for onclick handlers
window.saveSupplierReportSchedule = saveSupplierReportSchedule;
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

// Report Library Functions - Only show reports for this supplier
function loadReportLibrary() {
    console.log('Loading report library data for supplier...');
    // Load real data from backend filtered for supplier
    fetch('/supplier-reports/library?supplier_only=true', {
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
        // Show fallback data for suppliers
        const fallbackData = getMockSupplierReports();
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
        
        // Generate action buttons for suppliers (limited actions)
        const actionButtons = generateSupplierActionButtons(report);
        
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

// Generate action buttons specific for suppliers (view and download only)
function generateSupplierActionButtons(report) {
    let buttons = [];
    
    // Edit button - suppliers can edit their own reports
    buttons.push(`
        <button class="text-blue-600 hover:text-blue-900 text-sm" data-action="edit" data-report-id="${report.id}" data-report-name="${report.name}" title="Edit Schedule">
            <i class="fas fa-edit"></i>
        </button>
    `);
    
    // Status-based buttons (pause/resume)
    if (report.status === 'active') {
        buttons.push(`
            <button class="text-yellow-600 hover:text-yellow-900 text-sm" data-action="pause" data-report-id="${report.id}" data-report-name="${report.name}" title="Pause Schedule">
                <i class="fas fa-pause"></i>
            </button>
        `);
        
        // Generate Now button (if active)
        buttons.push(`
            <button class="text-green-600 hover:text-green-900 text-sm" data-action="generate" data-report-id="${report.id}" data-report-name="${report.name}" title="Generate Now">
                <i class="fas fa-cog"></i>
            </button>
        `);
    } else if (report.status === 'paused') {
        buttons.push(`
            <button class="text-blue-600 hover:text-blue-900 text-sm" data-action="resume" data-report-id="${report.id}" data-report-name="${report.name}" title="Resume Schedule">
                <i class="fas fa-play"></i>
            </button>
        `);
    }
    
    // Delete button - suppliers can delete their own report schedules
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

// Historical Reports Functions - Only show reports for this supplier
function loadHistoricalReports() {
    // Load real data from backend filtered for supplier
    fetch('/supplier-reports/historical?supplier_only=true', {
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
        // Show fallback data for suppliers
        const fallbackData = getMockSupplierHistoricalReports();
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
                <div class="text-sm text-gray-900">${report.file_size || 'Unknown'}</div>
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

// Ad-hoc Report Functions for Suppliers
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

// Toggle email recipients section (simplified for suppliers - they can only send to themselves)
function toggleEmailRecipientsSection() {
    // For suppliers, no additional recipients section needed since they can only send to themselves
    // This function exists to prevent errors but doesn't need to do anything
}

function updateDynamicFilters(reportType) {
    const filtersContainer = document.getElementById('filters-container');
    if (!filtersContainer) return;
    
    filtersContainer.innerHTML = '';

    const supplierFilterConfigs = {
        'supplier_inventory': [
            { label: 'Product Category', type: 'select', options: ['All', 'Raw Coffee'] }, // Suppliers only see raw coffee
            { label: 'Location', type: 'select', options: ['All', 'Main Warehouse', 'Secondary Storage'] }
        ],
        'supplier_orders': [
            { label: 'Order Status', type: 'select', options: ['All', 'Pending', 'Confirmed', 'Shipped', 'Delivered'] },
            { label: 'Customer Type', type: 'select', options: ['All', 'Wholesale', 'Retail'] }
        ],
        'supplier_quality': [
            { label: 'Quality Grade', type: 'select', options: ['All', 'Grade A', 'Grade B', 'Grade C'] },
            { label: 'Coffee Type', type: 'select', options: ['All', 'Arabica', 'Robusta'] }
        ],
        'supplier_deliveries': [
            { label: 'Delivery Status', type: 'select', options: ['All', 'Scheduled', 'In Transit', 'Delivered'] },
            { label: 'Destination', type: 'select', options: ['All', 'Local', 'Regional', 'International'] }
        ]
    };

    const filters = supplierFilterConfigs[reportType] || [];
    
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
        // Simulate progress steps
        const steps = [
            { progress: 20, text: 'Validating request...' },
            { progress: 40, text: 'Fetching your data...' },
            { progress: 60, text: 'Processing data...' },
            { progress: 80, text: 'Generating report...' },
            { progress: 100, text: 'Finalizing...' }
        ];
        
        for (const step of steps) {
            await new Promise(resolve => setTimeout(resolve, 800));
            progressBar.style.width = `${step.progress}%`;
            progressText.textContent = step.text;
        }
        
        // Show success
        await new Promise(resolve => setTimeout(resolve, 500));
        showGenerationSuccess(reportType, format, deliveryMethod);
        
    } catch (error) {
        console.error('Report generation failed:', error);
        showGenerationError('Failed to generate report. Please try again.');
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
    // For now, just show a message since this is a simulation
    showNotification('Report download would start here', 'info');
    closeAdhocModal();
}

function viewGeneratedReport() {
    // For now, just show a message since this is a simulation
    showNotification('Report viewer would open here', 'info');
    closeAdhocModal();
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
    document.getElementBy
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
        const response = await fetch(`/supplier-reports/${reportId}/generate`, {
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
        const downloadUrl = `/supplier-reports/${reportId}/download`;
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
        // Open report in new tab
        const viewUrl = `/supplier-reports/${reportId}/view`;
        window.open(viewUrl, '_blank');
        
        showNotification(`Opening "${reportName}" in new window...`, 'info');
    } catch (error) {
        console.error('Error viewing report:', error);
        showNotification('Failed to open report', 'error');
    }
}

// Additional Action Handler Functions for Suppliers
async function handleEditReport(reportId) {
    try {
        showNotification('Edit functionality coming soon...', 'info');
        // TODO: Implement edit modal for suppliers
        console.log('Edit report:', reportId);
    } catch (error) {
        console.error('Error editing report:', error);
        showNotification('Failed to edit report', 'error');
    }
}

async function handlePauseReport(reportId, reportName) {
    if (!confirm(`Pause the schedule for "${reportName}"?`)) return;
    
    try {
        const response = await fetch(`/supplier-reports/${reportId}/pause`, {
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
        const response = await fetch(`/supplier-reports/${reportId}/resume`, {
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
        const response = await fetch(`/supplier-reports/${reportId}`, {
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
            saveSupplierReportSchedule();
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

    // Update step content
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

function saveSupplierReportSchedule() {
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

    fetch('/supplier-reports', {
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
    document.getElementById('adhocReportModal').classList.remove('hidden');
}

function closeAdhocModal() {
    document.getElementById('adhocReportModal').classList.add('hidden');
}

// Unified Stats Card Management Functions
async function updateStatsCards() {
    try {
        const response = await fetch('/supplier-reports/stats?supplier_only=true', {
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
        
        if (data.success && data.data) {
            updateStatsCard('active-reports-card', data.data.activeReports || 0);
            updateStatsCard('generated-today-card', data.data.generatedToday || 0);
            updateStatsCard('total-reports-card', data.data.totalReports || 0);
            updateStatsCard('latest-report-card', data.data.lastGenerated || 'None');
        }
    } catch (error) {
        console.error('Error updating stats cards:', error);
    }
}

// Generic function to update any stats card with animation
function updateStatsCard(cardId, newValue) {
    const card = document.getElementById(cardId);
    
    if (card) {
        const valueElement = card.querySelector('p[data-value]');
        
        if (valueElement) {
            valueElement.textContent = newValue;
            valueElement.setAttribute('data-value', newValue);
            
            // Add update animation
            card.classList.add('transform', 'scale-105', 'transition-transform', 'duration-200');
            setTimeout(() => {
                card.classList.remove('transform', 'scale-105');
            }, 200);
        }
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

function getMockSupplierReports() {
    return [
        {
            id: 1,
            name: 'Supplier Inventory Report',
            type: 'inventory',
            format: 'pdf',
            frequency: 'Daily',
            last_generated: '2024-12-20',
            status: 'active',
            recipients: 'Current User',
            last_sent: '2024-12-20 08:00:00'
        },
        {
            id: 2,
            name: 'Quality Metrics Report',
            type: 'quality',
            format: 'excel',
            frequency: 'Weekly',
            last_generated: '2024-12-18',
            status: 'active',
            recipients: 'Current User',
            last_sent: '2024-12-18 09:00:00'
        },
        {
            id: 3,
            name: 'Delivery Performance Report',
            type: 'deliveries',
            format: 'pdf',
            frequency: 'Monthly',
            last_generated: '2024-12-01',
            status: 'paused',
            recipients: 'Current User',
            last_sent: '2024-12-01 10:00:00'
        }
    ];
}

// Mock data for supplier historical reports
function getMockSupplierHistoricalReports() {
    return [
        {
            id: 1,
            name: 'Monthly Sales Report',
            generated_for: 'My Account',
            generated_at: '2024-12-01',
            format: 'pdf',
            file_size: '1.2 MB',
            status: 'completed'
        },
        {
            id: 2,
            name: 'Inventory Status Report',
            generated_for: 'My Account',
            generated_at: '2024-11-15',
            format: 'excel',
            file_size: '850 KB',
            status: 'completed'
        },
        {
            id: 3,
            name: 'Quality Assurance Report',
            generated_for: 'My Account',
            generated_at: '2024-10-10',
            format: 'pdf',
            file_size: '600 KB',
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

