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
    setupActionButtonHandlers();
    setupSearchAndFilters();
    setupRecipientsModal();
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

// Refresh the currently active tab
function refreshCurrentTab() {
    // Find the currently active tab
    const activeTabButton = document.querySelector('.tab-button.bg-orange-100');
    
    if (activeTabButton) {
        const tabName = activeTabButton.textContent.trim().toLowerCase();
        
        if (tabName.includes('library')) {
            loadReportLibrary();
        } else if (tabName.includes('historical')) {
            loadHistoricalReports();
        }
    } else {
        // Default to library if no active tab found
        loadReportLibrary();
    }
}

// Report Library Functions
function loadReportLibrary() {
    // Load real data from backend instead of using mock data
    fetch('/reports/library', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.data) {
            updateLibraryTable(data.data);
        } else {
            console.error('No data received from backend');
            // Fallback to filtered mock data
            filterLibraryReports();
        }
    })
    .catch(error => {
        console.error('Error loading report library:', error);
        // Fallback to mock data if backend fails
        filterLibraryReports();
    });
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
                    <button class="text-orange-600 hover:text-orange-900 text-sm" title="Edit" data-action="edit" data-report-id="${report.id}" data-report-name="${report.name}">
                        <i class="fas fa-edit"></i>
                    </button>
                    ${report.status === 'active' ? 
                        `<button class="text-yellow-600 hover:text-yellow-900 text-sm" title="Pause" data-action="pause" data-report-id="${report.id}" data-report-name="${report.name}">
                            <i class="fas fa-pause"></i>
                        </button>` :
                        `<button class="text-blue-600 hover:text-blue-900 text-sm" title="Resume" data-action="resume" data-report-id="${report.id}" data-report-name="${report.name}">
                            <i class="fas fa-play"></i>
                        </button>`
                    }
                    <button class="text-green-600 hover:text-green-900 text-sm" title="Generate Now" data-action="generate" data-report-id="${report.id}" data-report-name="${report.name}">
                        <i class="fas fa-cog"></i>
                    </button>
                    <button class="text-red-600 hover:text-red-900 text-sm" title="Delete" data-action="delete" data-report-id="${report.id}" data-report-name="${report.name}">
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
    // Load real data from backend instead of using mock data
    fetch('/reports/historical', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.data) {
            updateHistoricalTable(data.data);
        } else {
            console.error('No historical data received from backend');
            // Fallback to filtered mock data
            filterHistoricalReports();
        }
    })
    .catch(error => {
        console.error('Error loading historical reports:', error);
        // Fallback to mock data if backend fails
        filterHistoricalReports();
    });
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
                    <button class="text-blue-600 hover:text-blue-900 text-sm" title="Download" data-action="download" data-report-id="${report.id}" data-report-name="${report.name}">
                        <i class="fas fa-download"></i>
                    </button>
                    <button class="text-green-600 hover:text-green-900 text-sm" title="View" data-action="view" data-report-id="${report.id}" data-report-name="${report.name}">
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

    // Setup delivery method radio buttons
    const deliveryRadios = document.querySelectorAll('input[name="delivery_method"]');
    deliveryRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleEmailRecipientsSection();
        });
    });

    // Load recipients for email delivery
    loadAdhocRecipients();

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

function toggleEmailRecipientsSection() {
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    const emailSection = document.getElementById('email-recipients-section');
    
    if (deliveryMethod === 'email' || deliveryMethod === 'both') {
        emailSection.classList.remove('hidden');
    } else {
        emailSection.classList.add('hidden');
    }
}

function loadAdhocRecipients() {
    const checkboxContainer = document.getElementById('recipients-checkbox-list');
    
    if (!checkboxContainer) return;
    
    // Load real data from backend instead of using mock data
    fetch('/reports/recipients', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Clear existing checkboxes
        checkboxContainer.innerHTML = '';
        
        // Transform backend data to match expected format
        const recipients = [];
        
        // Add users as recipients
        if (data.users) {
            data.users.forEach(user => {
                recipients.push({
                    id: `user_${user.id}`,
                    name: user.name,
                    email: user.email,
                    department: 'User'
                });
            });
        }
        
        // Add internal roles as recipients
        if (data.internal_roles) {
            data.internal_roles.forEach((role, index) => {
                recipients.push({
                    id: `role_${index}`,
                    name: role,
                    email: `${role.toLowerCase().replace(/\s+/g, '')}@beantrack.com`,
                    department: role
                });
            });
        }
        
        // Add suppliers as recipients (optional, depending on your needs)
        if (data.suppliers && data.suppliers.length > 0) {
            data.suppliers.forEach(supplier => {
                recipients.push({
                    id: `supplier_${supplier.id}`,
                    name: supplier.name,
                    email: `contact@${supplier.name.toLowerCase().replace(/\s+/g, '')}.com`,
                    department: 'Supplier'
                });
            });
        }
        
        // Populate checkboxes for recipients
        recipients.forEach(recipient => {
            const checkboxDiv = document.createElement('div');
            checkboxDiv.className = 'flex items-center';
            checkboxDiv.innerHTML = `
                <input type="checkbox" id="recipient-${recipient.id}" name="recipients[]" value="${recipient.id}" class="mr-3 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <label for="recipient-${recipient.id}" class="flex-1 text-sm text-gray-700 cursor-pointer">
                    <span class="font-medium">${recipient.name}</span>
                    <span class="text-gray-500 ml-1">(${recipient.email})</span>
                    <div class="text-xs text-gray-400">${recipient.department}</div>
                </label>
            `;
            checkboxContainer.appendChild(checkboxDiv);
        });
        
        // Setup select all / clear all functionality
        setupRecipientCheckboxControls();
    })
    .catch(error => {
        console.error('Error loading recipients:', error);
        // Fallback to mock data if backend fails
        const recipients = getMockRecipients();
        
        // Clear existing checkboxes
        checkboxContainer.innerHTML = '';
        
        // Populate checkboxes for recipients
        recipients.forEach(recipient => {
            const checkboxDiv = document.createElement('div');
            checkboxDiv.className = 'flex items-center';
            checkboxDiv.innerHTML = `
                <input type="checkbox" id="recipient-${recipient.id}" name="recipients[]" value="${recipient.id}" class="mr-3 rounded border-gray-300 text-orange-600 focus:ring-orange-500">
                <label for="recipient-${recipient.id}" class="flex-1 text-sm text-gray-700 cursor-pointer">
                    <span class="font-medium">${recipient.name}</span>
                    <span class="text-gray-500 ml-1">(${recipient.email})</span>
                    <div class="text-xs text-gray-400">${recipient.department}</div>
                </label>
            `;
            checkboxContainer.appendChild(checkboxDiv);
        });
        
        // Setup select all / clear all functionality
        setupRecipientCheckboxControls();
    });
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
    // Get form data
    const reportType = document.getElementById('report-type').value;
    const format = document.getElementById('output-format').value;
    const fromDate = document.getElementById('from-date').value;
    const toDate = document.getElementById('to-date').value;
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    
    // Validate required fields
    if (!reportType) {
        showNotification('Please select a report type', 'error');
        return;
    }
    
    if (!fromDate || !toDate) {
        showNotification('Please select date range', 'error');
        return;
    }
    
    if ((deliveryMethod === 'email' || deliveryMethod === 'both')) {
        const selectedCheckboxes = document.querySelectorAll('#recipients-checkbox-list input[type="checkbox"]:checked');
        if (selectedCheckboxes.length === 0) {
            showNotification('Please select at least one email recipient', 'error');
            return;
        }
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
        
        // Add selected recipients if email delivery
        if (deliveryMethod === 'email' || deliveryMethod === 'both') {
            const selectedCheckboxes = document.querySelectorAll('#recipients-checkbox-list input[type="checkbox"]:checked');
            selectedCheckboxes.forEach(checkbox => {
                formData.append('recipients[]', checkbox.value);
            });
        }
        
        // Add CSRF token
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Step 1: Initialize
        progressText.textContent = 'Initializing report generation...';
        progressBar.style.width = '10%';
        await new Promise(resolve => setTimeout(resolve, 500));
        
        // Step 2: Send request to backend
        progressText.textContent = 'Processing request...';
        progressBar.style.width = '30%';
        
        const response = await fetch('/reports/adhoc', {
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
    if (deliveryMethod === 'email' || deliveryMethod === 'both') {
        const selectedCheckboxes = document.querySelectorAll('#recipients-checkbox-list input[type="checkbox"]:checked');
        const count = selectedCheckboxes.length;
        const emailNoticeSpan = emailNotice.querySelector('span');
        
        if (count === 1) {
            // Get the name of the single selected recipient
            const selectedCheckbox = selectedCheckboxes[0];
            const label = document.querySelector(`label[for="${selectedCheckbox.id}"]`);
            const recipientName = label.querySelector('span.font-medium').textContent;
            emailNoticeSpan.textContent = `Report has been sent to ${recipientName}`;
        } else {
            emailNoticeSpan.textContent = `Report has been sent to ${count} recipients`;
        }
        
        emailNotice.classList.remove('hidden');
    } else {
        emailNotice.classList.add('hidden');
    }
    
    // Add to historical reports
    addToHistoricalReports(reportType, format, deliveryMethod);
    
    // Update stats cards to reflect the generated report
    updateStatsCards();
}

function showGenerationError(message) {
    const progressSection = document.getElementById('generation-progress');
    const errorSection = document.getElementById('generation-error');
    
    progressSection.classList.add('hidden');
    errorSection.classList.remove('hidden');
    
    document.getElementById('error-message').textContent = message;
}

function downloadGeneratedReport() {
    if (!window.currentReportId) {
        showNotification('No report available for download', 'error');
        return;
    }
    
    // Create a temporary link to trigger download
    const downloadUrl = `/reports/${window.currentReportId}/download`;
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
    if (!window.currentReportId) {
        showNotification('No report available for viewing', 'error');
        return;
    }
    
    // Open report in new tab
    const viewUrl = `/reports/${window.currentReportId}/view`;
    window.open(viewUrl, '_blank');
    
    showNotification('Opening report in new window...', 'info');
}

function retryReportGeneration() {
    const reportType = document.getElementById('report-type').value;
    const format = document.getElementById('output-format').value;
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    
    // Reset to progress view
    const progressSection = document.getElementById('generation-progress');
    const errorSection = document.getElementById('generation-error');
    
    errorSection.classList.add('hidden');
    progressSection.classList.remove('hidden');
    
    // Restart generation
    simulateReportGeneration(reportType, format, deliveryMethod);
}

function formatReportTypeName(reportType) {
    const typeNames = {
        'sales_data': 'Sales Data',
        'inventory_movements': 'Inventory Movements',
        'order_history': 'Order History',
        'production_batches': 'Production Batches',
        'supplier_performance': 'Supplier Performance',
        'quality_metrics': 'Quality Metrics'
    };
    return typeNames[reportType] || reportType;
}

function generateRandomFileSize() {
    const sizes = ['1.2 MB', '2.3 MB', '856 KB', '3.1 MB', '1.8 MB', '4.2 MB'];
    return sizes[Math.floor(Math.random() * sizes.length)];
}

function addToHistoricalReports(reportType, format, deliveryMethod) {
    // This would normally add the report to the backend
    // For demo purposes, we'll just refresh the historical tab if it's active
    const activeTab = document.querySelector('.tab-button.active');
    if (activeTab && activeTab.dataset.tab === 'historical') {
        filterHistoricalReports();
    }
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
    refreshCurrentTab();
    // Update stats cards to reflect the new report
    updateStatsCards();
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
        }
    });
}

// Action Handler Functions
async function handleEditReport(reportId) {
    try {
        showNotification('Loading report data...', 'info');
        
        // For demo purposes, use mock data if backend request fails
        try {
            const response = await fetch(`/reports/${reportId}/edit`, {
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
            
            // Find the report in mock data
            const mockReports = getMockReportLibraryData();
            const report = mockReports.find(r => r.id == reportId);
            
            if (!report) {
                showNotification('Report not found', 'error');
                return;
            }
            
            // Use mock data for demonstration
            const mockReport = {
                id: report.id,
                name: report.name,
                description: report.description,
                type: 'sales_data', // Keep existing type mapping
                format: report.type,
                frequency: report.frequency.toLowerCase(),
                recipients: [1, 2], // Use recipient IDs that match getMockRecipients
                status: report.status
            };
            
            openEditModal(mockReport, getMockTemplates(), getMockRecipients());
        }
    } catch (error) {
        console.error('Edit report error:', error);
        showNotification('Error loading report data', 'error');
    }
}

async function handleGenerateReport(reportId, reportName) {
    if (!confirm(`Generate report "${reportName}" now?`)) {
        return;
    }

    try {
        showNotification('Generating report...', 'info');
        
        const response = await fetch(`/reports/${reportId}/generate`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Report generation started successfully', 'success');
            // Refresh the reports table
            refreshCurrentTab();
            // Update stats cards to reflect the generated report
            updateStatsCards();
        } else {
            showNotification(result.message || 'Failed to generate report', 'error');
        }
    } catch (error) {
        console.error('Generate report error:', error);
        showNotification('Error generating report', 'error');
    }
}

async function handleDeleteReport(reportId, reportName) {
    if (!confirm(`Are you sure you want to delete the report "${reportName}"? This action cannot be undone.`)) {
        return;
    }

    try {
        showNotification('Deleting report...', 'info');
        
        const response = await fetch(`/reports/${reportId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Report deleted successfully', 'success');
            // Refresh the reports table
            refreshCurrentTab();
            // Update stats cards to reflect the change
            updateStatsCards();
        } else {
            showNotification(result.message || 'Failed to delete report', 'error');
        }
    } catch (error) {
        console.error('Delete report error:', error);
        showNotification('Error deleting report', 'error');
    }
}

async function handleDownloadReport(reportId, reportName) {
    try {
        showNotification('Preparing download...', 'info');
        
        // Create a temporary link to trigger download
        const downloadUrl = `/reports/${reportId}/download`;
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = '';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        showNotification('Download started', 'success');
    } catch (error) {
        console.error('Download report error:', error);
        showNotification('Error downloading report', 'error');
    }
}

async function handleViewReport(reportId, reportName) {
    try {
        showNotification('Opening report...', 'info');
        
        // Open report in new tab
        const viewUrl = `/reports/${reportId}/view`;
        window.open(viewUrl, '_blank');
        
    } catch (error) {
        console.error('View report error:', error);
        showNotification('Error opening report', 'error');
    }
}

// Pause and Resume Report Functions
async function handlePauseReport(reportId, reportName) {
    if (!confirm(`Are you sure you want to pause the report "${reportName}"?`)) {
        return;
    }

    try {
        showNotification('Pausing report...', 'info');
        
        const response = await fetch(`/reports/${reportId}/pause`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        // Check if we got redirected (probably to login)
        if (response.redirected) {
            showNotification('Session expired. Please refresh the page and try again.', 'error');
            return;
        }

        if (!response.ok) {
            // Even if response is not ok, check if the operation might have succeeded
            // We'll verify this by refreshing and checking the actual state
            console.warn('Response not OK, but checking if operation succeeded anyway...');
            setTimeout(() => {
                refreshCurrentTab();
                updateStatsCards();
            }, 1000);
            showNotification('Operation may have completed. Refreshing data...', 'info');
            return;
        }

        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // If we can't parse the response, assume success and refresh
            console.warn('Non-JSON response, assuming success and refreshing...');
            setTimeout(() => {
                refreshCurrentTab();
                updateStatsCards();
            }, 1000);
            showNotification(`Report "${reportName}" pause request sent. Refreshing data...`, 'info');
            return;
        }

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || `Report "${reportName}" has been paused`, 'success');
            // Refresh the table to show updated status with a small delay
            setTimeout(() => {
                refreshCurrentTab();
                updateStatsCards();
            }, 500);
        } else {
            showNotification(result.message || 'Failed to pause report', 'error');
        }
    } catch (error) {
        console.error('Pause report error:', error);
        // Even on error, try refreshing in case the operation succeeded
        setTimeout(() => {
            refreshCurrentTab();
            updateStatsCards();
        }, 1000);
        showNotification(`Pause request sent. Refreshing data...`, 'info');
    }
}

async function handleResumeReport(reportId, reportName) {
    if (!confirm(`Are you sure you want to resume the report "${reportName}"?`)) {
        return;
    }

    try {
        showNotification('Resuming report...', 'info');
        
        const response = await fetch(`/reports/${reportId}/resume`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        // Check if we got redirected (probably to login)
        if (response.redirected) {
            showNotification('Session expired. Please refresh the page and try again.', 'error');
            return;
        }

        if (!response.ok) {
            // Even if response is not ok, check if the operation might have succeeded
            // We'll verify this by refreshing and checking the actual state
            console.warn('Response not OK, but checking if operation succeeded anyway...');
            setTimeout(() => {
                refreshCurrentTab();
                updateStatsCards();
            }, 1000);
            showNotification('Operation may have completed. Refreshing data...', 'info');
            return;
        }

        // Check if response is actually JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // If we can't parse the response, assume success and refresh
            console.warn('Non-JSON response, assuming success and refreshing...');
            setTimeout(() => {
                refreshCurrentTab();
                updateStatsCards();
            }, 1000);
            showNotification(`Report "${reportName}" resume request sent. Refreshing data...`, 'info');
            return;
        }

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || `Report "${reportName}" has been resumed`, 'success');
            // Refresh the table to show updated status with a small delay
            setTimeout(() => {
                refreshCurrentTab();
                updateStatsCards();
            }, 500);
        } else {
            showNotification(result.message || 'Failed to resume report', 'error');
        }
    } catch (error) {
        console.error('Resume report error:', error);
        // Even on error, try refreshing in case the operation succeeded
        setTimeout(() => {
            refreshCurrentTab();
            updateStatsCards();
        }, 1000);
        showNotification(`Resume request sent. Refreshing data...`, 'info');
    }
}

function updateReportRowStatus(reportId, newStatus) {
    const tableBody = document.getElementById('library-tbody');
    if (!tableBody) return;
    
    // Find the row with the matching report ID
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach(row => {
        const editButton = row.querySelector('[data-action="edit"]');
        if (editButton && editButton.dataset.reportId == reportId) {
            // Update status badge
            const statusCell = row.cells[5]; // Status is the 6th column (index 5)
            if (statusCell) {
                statusCell.innerHTML = getStatusBadge(newStatus);
            }
            
            // Update action buttons
            const actionCell = row.cells[6]; // Actions is the 7th column (index 6)
            if (actionCell) {
                const pauseResumeButton = actionCell.querySelector('[data-action="pause"], [data-action="resume"]');
                if (pauseResumeButton) {
                    const reportName = editButton.dataset.reportName;
                    if (newStatus === 'active') {
                        pauseResumeButton.outerHTML = `<button class="text-yellow-600 hover:text-yellow-900 text-sm" title="Pause" data-action="pause" data-report-id="${reportId}" data-report-name="${reportName}">
                            <i class="fas fa-pause"></i>
                        </button>`;
                    } else {
                        pauseResumeButton.outerHTML = `<button class="text-blue-600 hover:text-blue-900 text-sm" title="Resume" data-action="resume" data-report-id="${reportId}" data-report-name="${reportName}">
                            <i class="fas fa-play"></i>
                        </button>`;
                    }
                }
            }
        }
    });
}

// Edit Modal Functions
function openEditModal(report, templates, recipients) {
    // Create edit modal if it doesn't exist
    let editModal = document.getElementById('edit-report-modal');
    if (!editModal) {
        createEditModal();
        editModal = document.getElementById('edit-report-modal');
    }
    
    // Populate form with report data
    populateEditForm(report, templates, recipients);
    
    // Show modal
    editModal.classList.remove('hidden');
}

function createEditModal() {
    const modalHTML = `
        <div id="edit-report-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
                    <div class="flex items-center justify-between p-6 border-b">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Report</h3>
                        <button id="close-edit-modal" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <form id="edit-report-form" class="p-6">
                        <input type="hidden" id="edit-report-id" name="report_id">
                        
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Report Name</label>
                                <input type="text" id="edit-report-name" name="name" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="edit-report-description" name="description" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"></textarea>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select id="edit-report-type" name="type" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                        <option value="">Select Type</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                                    <select id="edit-report-format" name="format" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                        <option value="pdf">PDF</option>
                                        <option value="excel">Excel</option>
                                        <option value="csv">CSV</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                                <select id="edit-report-frequency" name="frequency" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                    <option value="daily">Daily</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="quarterly">Quarterly</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                                <select id="edit-report-recipients" name="recipients[]" multiple class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500" required>
                                </select>
                                <p class="text-sm text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple recipients</p>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6 pt-6 border-t">
                            <button type="button" id="cancel-edit" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-md">
                                Update Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Add event listeners
    document.getElementById('close-edit-modal').addEventListener('click', () => {
        document.getElementById('edit-report-modal').classList.add('hidden');
    });
    
    document.getElementById('cancel-edit').addEventListener('click', () => {
        document.getElementById('edit-report-modal').classList.add('hidden');
    });
    
    document.getElementById('edit-report-form').addEventListener('submit', handleEditFormSubmit);
}

function populateEditForm(report, templates, recipients) {
    document.getElementById('edit-report-id').value = report.id;
    document.getElementById('edit-report-name').value = report.name || '';
    document.getElementById('edit-report-description').value = report.description || '';
    document.getElementById('edit-report-format').value = report.format || '';
    document.getElementById('edit-report-frequency').value = report.frequency || '';
    
    // Populate report types
    const typeSelect = document.getElementById('edit-report-type');
    typeSelect.innerHTML = '<option value="">Select Type</option>';
    templates.forEach(template => {
        const option = document.createElement('option');
        option.value = template.type;
        option.textContent = template.name;
        option.selected = template.type === report.type;
        typeSelect.appendChild(option);
    });
    
    // Populate recipients
    const recipientsSelect = document.getElementById('edit-report-recipients');
    recipientsSelect.innerHTML = '';
    const reportRecipients = typeof report.recipients === 'string' ? JSON.parse(report.recipients) : (report.recipients || []);
    
    recipients.forEach(recipient => {
        const option = document.createElement('option');
        option.value = recipient.id;
        option.textContent = `${recipient.name} (${recipient.email})`;
        option.selected = reportRecipients.includes(recipient.id.toString()) || reportRecipients.includes(recipient.id);
        recipientsSelect.appendChild(option);
    });
}

async function handleEditFormSubmit(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const reportId = formData.get('report_id');
    
    // Convert FormData to JSON
    const data = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'recipients[]') {
            if (!data.recipients) data.recipients = [];
            data.recipients.push(value);
        } else {
            data[key] = value;
        }
    }
    
    try {
        showNotification('Updating report...', 'info');
        
        const response = await fetch(`/reports/${reportId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Report updated successfully', 'success');
            document.getElementById('edit-report-modal').classList.add('hidden');
            // Refresh the reports table
            refreshCurrentTab();
            // Update stats cards to reflect any changes
            updateStatsCards();
        } else {
            if (result.errors) {
                let errorMessage = 'Validation errors:\n';
                Object.keys(result.errors).forEach(field => {
                    errorMessage += `${field}: ${result.errors[field].join(', ')}\n`;
                });
                showNotification(errorMessage, 'error');
            } else {
                showNotification(result.message || 'Failed to update report', 'error');
            }
        }
    } catch (error) {
        console.error('Update report error:', error);
        showNotification('Error updating report', 'error');
    }
}

// Recipients Management Functions
function openRecipientsModal() {
    document.getElementById('recipientsModal').classList.remove('hidden');
    loadRecipients();
}

function closeRecipientsModal() {
    document.getElementById('recipientsModal').classList.add('hidden');
}

function openRecipientFormModal(recipientId = null) {
    const modal = document.getElementById('recipientFormModal');
    const title = document.getElementById('recipient-form-title');
    const form = document.getElementById('recipient-form');
    
    if (recipientId) {
        title.textContent = 'Edit Recipient';
        // Load recipient data for editing
        const recipients = getExtendedMockRecipients();
        const recipient = recipients.find(r => r.id == recipientId);
        if (recipient) {
            document.getElementById('recipient-id').value = recipient.id;
            document.getElementById('recipient-name').value = recipient.name;
            document.getElementById('recipient-email').value = recipient.email;
            document.getElementById('recipient-department').value = recipient.department;
            document.getElementById('recipient-status').value = recipient.status;
        }
    } else {
        title.textContent = 'Add Recipient';
        form.reset();
        document.getElementById('recipient-id').value = '';
    }
    
    modal.classList.remove('hidden');
}

function closeRecipientFormModal() {
    document.getElementById('recipientFormModal').classList.add('hidden');
}

function loadRecipients() {
    // Load real data from backend instead of using mock data
    fetch('/reports/recipients', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Transform backend data to match expected format
        const recipients = [];
        
        // Add users as recipients
        if (data.users) {
            data.users.forEach(user => {
                recipients.push({
                    id: `user_${user.id}`,
                    name: user.name,
                    email: user.email,
                    department: 'User',
                    status: 'active',
                    type: 'user'
                });
            });
        }
        
        // Add internal roles as recipients
        if (data.internal_roles) {
            data.internal_roles.forEach((role, index) => {
                recipients.push({
                    id: `role_${index}`,
                    name: role,
                    email: `${role.toLowerCase().replace(/\s+/g, '')}@beantrack.com`,
                    department: role,
                    status: 'active',
                    type: 'role'
                });
            });
        }
        
        // Add suppliers as recipients
        if (data.suppliers) {
            data.suppliers.forEach(supplier => {
                recipients.push({
                    id: `supplier_${supplier.id}`,
                    name: supplier.name,
                    email: `contact@${supplier.name.toLowerCase().replace(/\s+/g, '')}.com`,
                    department: 'Supplier',
                    status: 'active',
                    type: 'supplier'
                });
            });
        }
        
        updateRecipientsTable(recipients);
    })
    .catch(error => {
        console.error('Error loading recipients:', error);
        // Fallback to mock data if backend fails
        const recipients = getExtendedMockRecipients();
        updateRecipientsTable(recipients);
    });
}

function updateRecipientsTable(recipients) {
    const tbody = document.getElementById('recipients-tbody');
    tbody.innerHTML = '';

    recipients.forEach(recipient => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${recipient.name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${recipient.email}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">${recipient.department}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${
                    recipient.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                }">
                    ${recipient.status.charAt(0).toUpperCase() + recipient.status.slice(1)}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <button class="text-orange-600 hover:text-orange-900 mr-3" onclick="openRecipientFormModal(${recipient.id})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="text-red-600 hover:text-red-900" onclick="deleteRecipient(${recipient.id}, '${recipient.name}')" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function getExtendedMockRecipients() {
    return [
        { id: 1, name: 'John Doe', email: 'john@beantrack.com', department: 'Finance' },
        { id: 2, name: 'Jane Smith', email: 'jane@beantrack.com', department: 'Operations' },
        { id: 3, name: 'Mike Johnson', email: 'mike@beantrack.com', department: 'Sales' },
        { id: 4, name: 'Sarah Wilson', email: 'sarah@beantrack.com', department: 'Logistics' }
    ];
}

function deleteRecipient(recipientId, recipientName) {
    if (!confirm(`Are you sure you want to delete the recipient "${recipientName}"? This action cannot be undone.`)) {
        return;
    }

    try {
        showNotification(`Recipient "${recipientName}" has been deleted`, 'success');
        loadRecipients(); // Refresh the table
    } catch (error) {
        console.error('Delete recipient error:', error);
        showNotification('Error deleting recipient', 'error');
    }
}

// Setup modal event listeners
function setupRecipientsModal() {
    // Close recipients modal
    document.getElementById('close-recipients-modal').addEventListener('click', closeRecipientsModal);
    
    // Add recipient button
    document.getElementById('add-recipient-btn').addEventListener('click', () => openRecipientFormModal());
    
    // Close recipient form modal
    document.getElementById('close-recipient-form-modal').addEventListener('click', closeRecipientFormModal);
    document.getElementById('cancel-recipient').addEventListener('click', closeRecipientFormModal);
    
    // Recipient form submission
    document.getElementById('recipient-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const recipientId = formData.get('recipient_id');
        const isEdit = recipientId && recipientId !== '';
        
        const recipientData = {
            name: formData.get('name'),
            email: formData.get('email'),
            department: formData.get('department'),
            status: formData.get('status')
        };
        
        try {
            if (isEdit) {
                showNotification(`Recipient "${recipientData.name}" has been updated`, 'success');
            } else {
                showNotification(`Recipient "${recipientData.name}" has been added`, 'success');
            }
            
            closeRecipientFormModal();
            loadRecipients(); // Refresh the table
        } catch (error) {
            console.error('Save recipient error:', error);
            showNotification('Error saving recipient', 'error');
        }
    });
}

// Stats Card Update Functions
async function updateStatsCards() {
    try {
        const response = await fetch('/reports/stats', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const result = await response.json();
        
        if (result.success && result.data) {
            updateActiveReportsCard(result.data.activeReports);
            updateGeneratedTodayCard(result.data.generatedToday);
            updatePendingReportsCard(result.data.pendingReports);
            updateSuccessRateCard(result.data.successRate);
        } else {
            console.warn('Stats update failed:', result.message);
        }
    } catch (error) {
        console.error('Error updating stats cards:', error);
    }
}

function updateActiveReportsCard(newValue) {
    updateStatsCard('active-reports-card', newValue);
}

function updateGeneratedTodayCard(newValue) {
    updateStatsCard('generated-today-card', newValue);
}

function updatePendingReportsCard(newValue) {
    updateStatsCard('pending-reports-card', newValue);
}

function updateSuccessRateCard(newValue) {
    updateStatsCard('success-rate-card', `${newValue}%`);
}

// Generic function to update any stats card
function updateStatsCard(cardId, newValue) {
    const card = document.getElementById(cardId);
    
    if (card) {
        const valueElement = card.querySelector('[data-value]');
        
        if (valueElement) {
            // Add animation class
            valueElement.style.transition = 'all 0.3s ease';
            valueElement.style.transform = 'scale(1.1)';
            
            // Update the value
            valueElement.textContent = newValue;
            valueElement.setAttribute('data-value', newValue);
            
            // Reset animation
            setTimeout(() => {
                valueElement.style.transform = 'scale(1)';
            }, 300);
        }
    }
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

// Search and Filter Functions
function setupSearchAndFilters() {
    // Library search and filters
    const librarySearch = document.getElementById('library-search');
    const libraryTypeFilter = document.getElementById('library-type-filter');
    const libraryFrequencyFilter = document.getElementById('library-frequency-filter');
    
    if (librarySearch) {
        librarySearch.addEventListener('input', debounce(filterLibraryReports, 300));
    }
    if (libraryTypeFilter) {
        libraryTypeFilter.addEventListener('change', filterLibraryReports);
    }
    if (libraryFrequencyFilter) {
        libraryFrequencyFilter.addEventListener('change', filterLibraryReports);
    }
    
    // Historical search and filters
    const historicalSearch = document.getElementById('historical-search');
    const historicalRecipientFilter = document.getElementById('historical-recipient-filter');
    const historicalFromDate = document.getElementById('historical-from-date');
    const historicalToDate = document.getElementById('historical-to-date');
    
    if (historicalSearch) {
        historicalSearch.addEventListener('input', debounce(filterHistoricalReports, 300));
    }
    if (historicalRecipientFilter) {
        historicalRecipientFilter.addEventListener('change', filterHistoricalReports);
    }
    if (historicalFromDate) {
        historicalFromDate.addEventListener('change', filterHistoricalReports);
    }
    if (historicalToDate) {
        historicalToDate.addEventListener('change', filterHistoricalReports);
    }
}

function filterLibraryReports() {
    const searchTerm = document.getElementById('library-search')?.value.toLowerCase() || '';
    const typeFilter = document.getElementById('library-type-filter')?.value || 'all';
    const frequencyFilter = document.getElementById('library-frequency-filter')?.value || 'all';
    
    // Get original mock data with more entries for better filtering demo
    const mockReports = getMockReportLibraryData();
    
    // Apply filters (removed state overrides since we now use real backend data)
    let filteredReports = mockReports.filter(report => {
        const matchesSearch = !searchTerm || 
            report.name.toLowerCase().includes(searchTerm) ||
            report.description.toLowerCase().includes(searchTerm) ||
            report.recipients.toLowerCase().includes(searchTerm);
            
        const matchesType = typeFilter === 'all' || report.type === typeFilter;
        
        const matchesFrequency = frequencyFilter === 'all' || 
            report.frequency.toLowerCase() === frequencyFilter.toLowerCase();
        
        return matchesSearch && matchesType && matchesFrequency;
    });
    
    updateLibraryTable(filteredReports);
}

function filterHistoricalReports() {
    const searchTerm = document.getElementById('historical-search')?.value.toLowerCase() || '';
    const recipientFilter = document.getElementById('historical-recipient-filter')?.value || 'all';
    const fromDate = document.getElementById('historical-from-date')?.value || '';
    const toDate = document.getElementById('historical-to-date')?.value || '';
    
    // Get original mock data with more entries
    const mockHistoricalReports = [
        {
            id: 'R00001',
            name: 'Monthly Supplier Demand Forecast',
            generated_for: 'Finance Dept',
            date_generated: '2024-01-15',
            format: 'PDF',
            size: '2.3 MB',
            status: 'completed'
        },
        {
            id: 'R00002',
            name: 'Weekly Production Efficiency',
            generated_for: 'Production Team',
            date_generated: '2024-01-20',
            format: 'Excel',
            size: '1.8 MB',
            status: 'completed'
        },
        {
            id: 'R00003',
            name: 'Daily Sales Summary',
            generated_for: 'Sales Team',
            date_generated: '2024-01-22',
            format: 'PDF',
            size: '0.9 MB',
            status: 'completed'
        },
        {
            id: 'R00004',
            name: 'Monthly Financial Report',
            generated_for: 'Finance Dept',
            date_generated: '2024-01-10',
            format: 'Excel',
            size: '3.1 MB',
            status: 'completed'
        },
        {
            id: 'R00005',
            name: 'Quarterly Inventory Analysis',
            generated_for: 'Production Team',
            date_generated: '2024-01-05',
            format: 'PDF',
            size: '2.7 MB',
            status: 'completed'
        }
    ];
    
    // Apply filters
    let filteredReports = mockHistoricalReports.filter(report => {
        const matchesSearch = !searchTerm || 
            report.name.toLowerCase().includes(searchTerm) ||
            report.generated_for.toLowerCase().includes(searchTerm);
            
        const matchesRecipient = recipientFilter === 'all' || 
            report.generated_for.toLowerCase().includes(recipientFilter.toLowerCase());
        
        let matchesDateRange = true;
        if (fromDate) {
            matchesDateRange = matchesDateRange && report.date_generated >= fromDate;
        }
        if (toDate) {
            matchesDateRange = matchesDateRange && report.date_generated <= toDate;
        }
        
        return matchesSearch && matchesRecipient && matchesDateRange;
    });
    
    updateHistoricalTable(filteredReports);
}

// Debounce utility function for search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Helper functions for mock data
function getMockReportLibraryData() {
    return [
        {
            id: 'R00001',
            name: 'Monthly Supplier Demand Forecast',
            description: 'Comprehensive analysis of supplier demand patterns',
            type: 'pdf',
            frequency: 'Monthly',
            recipients: 'Finance Dept, Logistics Team',
            last_generated: '2024-01-15',
            status: 'active'
        },
        {
            id: 'R00002',
            name: 'Weekly Production Efficiency',
            description: 'Production metrics and efficiency analysis',
            type: 'excel',
            frequency: 'Weekly',
            recipients: 'Production Team',
            last_generated: '2024-01-22',
            status: 'active'
        },
        {
            id: 'R00003',
            name: 'Daily Sales Summary',
            description: 'Daily sales performance and metrics',
            type: 'pdf',
            frequency: 'Daily',
            recipients: 'Sales Team',
            last_generated: '2024-01-23',
            status: 'active'
        },
        {
            id: 'R00004',
            name: 'Quarterly Financial Report',
            description: 'Comprehensive quarterly financial analysis',
            type: 'excel',
            frequency: 'Quarterly',
            recipients: 'Finance Dept, Management',
            last_generated: '2024-01-01',
            status: 'paused'
        },
        {
            id: 'R00005',
            name: 'Monthly Inventory Analysis',
            description: 'Monthly review of inventory levels and movements',
            type: 'dashboard',
            frequency: 'Monthly',
            recipients: 'Operations Team',
            last_generated: '2024-01-14',
            status: 'active'
        }
    ];
}

function getMockTemplates() {
    return [
        { id: 1, name: 'Default Template', description: 'Standard report template' },
        { id: 2, name: 'Detailed Template', description: 'Comprehensive detailed template' },
        { id: 3, name: 'Summary Template', description: 'Brief summary template' },
        { id: 4, name: 'Executive Template', description: 'Executive summary template' }
    ];
}

function getMockRecipients() {
    return [
        { id: 1, name: 'John Doe', email: 'john@beantrack.com', department: 'Finance' },
        { id: 2, name: 'Jane Smith', email: 'jane@beantrack.com', department: 'Operations' },
        { id: 3, name: 'Mike Johnson', email: 'mike@beantrack.com', department: 'Sales' },
        { id: 4, name: 'Sarah Wilson', email: 'sarah@beantrack.com', department: 'Logistics' }
    ];
}

// Global variable to track current report states
// (Removed - now using real backend data instead of client-side state tracking)

function setupRecipientCheckboxControls() {
    const selectAllBtn = document.getElementById('select-all-recipients');
    const clearAllBtn = document.getElementById('clear-all-recipients');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('#recipients-checkbox-list input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
        });
    }
    
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('#recipients-checkbox-list input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
        });
    }
}
