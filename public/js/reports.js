// Global variables
let currentStep = 1;
let selectedTemplate = '';
let selectedFormat = '';

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    setupTabNavigation();
    loadReportLibrary();
    loadHistoricalReports();
    setupAdhocForm();
    setupCreateReportModal();
});

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
        btn.classList.remove('active', 'border-light-brown', 'text-light-brown');
        btn.classList.add('border-transparent', 'text-mild-gray');
    });
    
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('active', 'border-light-brown', 'text-light-brown');
        activeButton.classList.remove('border-transparent', 'text-mild-gray');
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

// Report Library Functions
function loadReportLibrary() {
    // Mock data for demonstration
    const mockReports = [
        {
            name: 'Monthly Supplier Demand Forecast',
            description: 'Comprehensive analysis of supplier demand patterns',
            type: 'pdf',
            frequency: 'Monthly',
            recipients: 'Finance Dept, Logistics Team',
            last_generated: '2024-01-15',
            status: 'active'
        },
        {
            name: 'Weekly Production Efficiency',
            description: 'Production metrics and efficiency analysis',
            type: 'excel',
            frequency: 'Weekly',
            recipients: 'Production Team',
            last_generated: '2024-01-22',
            status: 'active'
        }
    ];
    
    updateLibraryTable(mockReports);
}

function updateLibraryTable(reports) {
    const tbody = document.getElementById('library-tbody');
    tbody.innerHTML = '';

    if (reports.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">No reports found</td></tr>';
        return;
    }

    reports.forEach(report => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-4 py-3">
                <div class="wrap-text">
                    <div class="text-sm font-medium text-gray-900">${report.name}</div>
                    <div class="text-sm text-gray-500">${report.description}</div>
                </div>
            </td>
            <td class="px-4 py-3">
                ${getFormatBadge(report.type)}
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.frequency}</span>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.recipients}</span>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.last_generated}</span>
            </td>
            <td class="px-4 py-3">
                ${getStatusBadge(report.status)}
            </td>
            <td class="px-4 py-3">
                <div class="action-buttons">
                    <button class="text-orange-600 hover:text-orange-900 text-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm" title="Generate">
                        <i class="fas fa-play"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-900 text-sm" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Historical Reports Functions
function loadHistoricalReports() {
    // Mock data for demonstration
    const mockHistoricalReports = [
        {
            name: 'Monthly Supplier Demand Forecast',
            generated_for: 'Finance Dept',
            date_generated: '2024-01-15',
            format: 'PDF',
            size: '2.3 MB',
            status: 'completed'
        }
    ];
    
    updateHistoricalTable(mockHistoricalReports);
}

function updateHistoricalTable(reports) {
    const tbody = document.getElementById('historical-tbody');
    tbody.innerHTML = '';

    if (reports.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-8 text-gray-500">No historical reports found</td></tr>';
        return;
    }

    reports.forEach(report => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.name}</span>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.generated_for}</span>
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.date_generated}</span>
            </td>
            <td class="px-4 py-3">
                ${getFormatBadge(report.format.toLowerCase())}
            </td>
            <td class="px-4 py-3">
                <span class="text-sm text-gray-900">${report.size}</span>
            </td>
            <td class="px-4 py-3">
                ${getStatusBadge(report.status)}
            </td>
            <td class="px-4 py-3">
                <div class="action-buttons">
                    <button class="text-blue-600 hover:text-blue-900 text-sm" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Ad-hoc Report Functions
function setupAdhocForm() {
    const reportTypeSelect = document.getElementById('report-type');
    
    if (reportTypeSelect) {
        reportTypeSelect.addEventListener('change', function() {
            updateDynamicFilters(this.value);
        });
    }

    const adhocForm = document.getElementById('adhoc-form');
    if (adhocForm) {
        adhocForm.addEventListener('submit', function(e) {
            e.preventDefault();
            generateAdhocReport();
        });
    }
}

function updateDynamicFilters(reportType) {
    const filtersContainer = document.getElementById('filters-container');
    if (!filtersContainer) return;
    
    filtersContainer.innerHTML = '';

    const filterConfigs = {
        'sales_data': [
            { label: 'Product Category', type: 'select', options: ['All', 'Arabica', 'Robusta', 'Blends'] },
            { label: 'Sales Channel', type: 'select', options: ['All', 'Retail', 'Wholesale', 'Online'] }
        ],
        'inventory_movements': [
            { label: 'Warehouse Location', type: 'select', options: ['All', 'Warehouse A', 'Warehouse B', 'Warehouse C'] },
            { label: 'Movement Type', type: 'select', options: ['All', 'Inbound', 'Outbound', 'Transfer'] }
        ]
    };

    const filters = filterConfigs[reportType] || [];
    
    filters.forEach(filter => {
        const filterDiv = document.createElement('div');
        filterDiv.innerHTML = `
            <label class="block text-sm font-medium text-gray-700 mb-1">${filter.label}</label>
            <select class="block w-full border border-gray-300 rounded-md px-3 py-2 bg-white focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                ${filter.options.map(option => `<option value="${option.toLowerCase()}">${option}</option>`).join('')}
            </select>
        `;
        filtersContainer.appendChild(filterDiv);
    });
}

function generateAdhocReport() {
    showNotification('Ad-hoc report generation started!', 'success');
}

// Create Report Modal Functions
function setupCreateReportModal() {
    // Template selection
    document.querySelectorAll('.template-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.template-option').forEach(opt => 
                opt.classList.remove('border-light-brown'));
            this.classList.add('border-light-brown');
            selectedTemplate = this.getAttribute('data-template');
        });
    });

    // Format selection
    document.querySelectorAll('.format-option').forEach(option => {
        option.addEventListener('click', function() {
            document.querySelectorAll('.format-option').forEach(opt => 
                opt.classList.remove('border-light-brown'));
            this.classList.add('border-light-brown');
            selectedFormat = this.getAttribute('data-format');
        });
    });
}

function openCreateReportModal() {
    document.getElementById('createReportModal').classList.remove('hidden');
    currentStep = 1;
    updateWizardStep();
}

function closeCreateReportModal() {
    document.getElementById('createReportModal').classList.add('hidden');
    currentStep = 1;
}

function nextStep() {
    if (validateCurrentStep()) {
        currentStep++;
        updateWizardStep();
        if (currentStep === 5) {
            updateReviewData();
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardStep();
    }
}

function updateWizardStep() {
    // Update step indicators
    document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
        const stepNumber = index + 1;
        const circle = indicator.querySelector('div');
        
        if (stepNumber < currentStep) {
            circle.className = 'w-8 h-8 bg-green-600 text-white rounded-full flex items-center justify-center text-sm font-medium';
            circle.innerHTML = '<i class="fas fa-check"></i>';
        } else if (stepNumber === currentStep) {
            circle.className = 'w-8 h-8 bg-light-brown text-white rounded-full flex items-center justify-center text-sm font-medium';
            circle.textContent = stepNumber;
        } else {
            circle.className = 'w-8 h-8 bg-light-background text-mild-gray rounded-full flex items-center justify-center text-sm font-medium';
            circle.textContent = stepNumber;
        }
    });

    // Update step content
    document.querySelectorAll('.wizard-step').forEach(step => {
        step.classList.remove('active');
    });
    const activeStep = document.querySelector(`[data-step="${currentStep}"]`);
    if (activeStep) {
        activeStep.classList.add('active');
    }

    // Update buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const saveBtn = document.getElementById('save-btn');

    if (prevBtn) prevBtn.classList.toggle('hidden', currentStep === 1);
    if (nextBtn) nextBtn.classList.toggle('hidden', currentStep === 5);
    if (saveBtn) saveBtn.classList.toggle('hidden', currentStep !== 5);
}

function validateCurrentStep() {
    switch (currentStep) {
        case 1:
            if (!selectedTemplate) {
                showNotification('Please select a report template', 'error');
                return false;
            }
            break;
        case 4:
            if (!selectedFormat) {
                showNotification('Please select a format', 'error');
                return false;
            }
            break;
    }
    return true;
}

function updateReviewData() {
    const elements = {
        'review-template': selectedTemplate,
        'review-format': selectedFormat.toUpperCase(),
        'review-frequency': document.getElementById('frequency')?.value || '',
        'review-time': document.getElementById('schedule-time')?.value || '',
        'review-day': document.getElementById('schedule-day')?.value || '',
        'review-recipients': Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
            .map(input => input.value).join(', ') || 'None selected'
    };

    Object.entries(elements).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    });
}

function saveReportSchedule() {
    showNotification('Report schedule saved successfully!', 'success');
    closeCreateReportModal();
    loadReportLibrary();
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

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 z-50`;
    
    const bgColor = {
        'success': 'bg-green-50 border-green-200',
        'error': 'bg-red-50 border-red-200',
        'warning': 'bg-yellow-50 border-yellow-200',
        'info': 'bg-blue-50 border-blue-200'
    }[type] || 'bg-gray-50 border-gray-200';

    const iconColor = {
        'success': 'text-green-400',
        'error': 'text-red-400',
        'warning': 'text-yellow-400',
        'info': 'text-blue-400'
    }[type] || 'text-gray-400';

    const icon = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    }[type] || 'fas fa-info-circle';

    notification.innerHTML = `
        <div class="p-4 ${bgColor} border rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="${icon} ${iconColor}"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-gray-800">${message}</p>
                </div>
                <div class="ml-auto pl-3">
                    <button class="text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
