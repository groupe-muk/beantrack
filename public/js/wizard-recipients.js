// Load recipients dynamically for the wizard
async function loadRecipientsForWizard() {
    console.log('loadRecipientsForWizard called');
    
    const container = document.getElementById('recipients-container');
    console.log('Container found:', container);
    
    if (!container) {
        console.error('Recipients container not found!');
        return;
    }

    // Clear loading message
    container.innerHTML = '';

    try {
        let data;
        
        // Always check for currentRecipientsData first to maintain synchronization
        if (window.currentRecipientsData && window.currentRecipientsData.length > 0) {
            console.log('Using currentRecipientsData from reports.js:', window.currentRecipientsData);
            data = transformCurrentRecipientsData(window.currentRecipientsData);
        } else {
            console.log('No currentRecipientsData found, fetching fresh data from API');
            // Fallback to API if no shared data available
            const response = await fetch('/reports/recipients', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            data = await response.json();
            
            // Also populate the global currentRecipientsData for consistency
            if (data && (data.users || data.internal_roles)) {
                const transformedData = [];
                
                // Add users
                if (data.users) {
                    data.users.forEach(user => {
                        transformedData.push({
                            id: `user_${user.id}`,
                            name: user.name,
                            email: user.email,
                            department: user.role || 'User',
                            role: user.role,
                            type: 'user',
                            status: 'active'
                        });
                    });
                }
                
                // Note: No longer processing internal_roles since we're focusing on individual users only
                
                // Update both local and global references
                window.currentRecipientsData = transformedData;
                console.log('Populated currentRecipientsData from API:', transformedData);
            }
        }
        
        console.log('Recipients data received:', data);
        
        // Add control buttons at the top
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'flex justify-between items-center mb-4 pb-2 border-b border-gray-200';
        controlsDiv.innerHTML = `
            <span class="text-sm font-medium text-gray-700">Select Recipients</span>
            <div class="space-x-2">
                <button type="button" id="wizard-select-all" class="text-xs text-blue-600 hover:text-blue-800 px-2 py-1 border border-blue-300 rounded">Select All</button>
                <button type="button" id="wizard-clear-all" class="text-xs text-red-600 hover:text-red-800 px-2 py-1 border border-red-300 rounded">Clear All</button>
                <button type="button" id="wizard-refresh" class="text-xs text-purple-600 hover:text-purple-800 px-2 py-1 border border-purple-300 rounded">
                    <i class="fas fa-sync-alt mr-1"></i>Refresh
                </button>
            </div>
        `;
        container.appendChild(controlsDiv);

        // Add individual users
        if (data.users && data.users.length > 0) {
            console.log('Adding users:', data.users);
            
            const usersHeader = document.createElement('h5');
            usersHeader.className = 'text-sm font-medium text-gray-700 mb-2 mt-4';
            usersHeader.textContent = 'Recipients';
            container.appendChild(usersHeader);

            data.users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'flex items-center recipient-item py-2 px-3 hover:bg-gray-50 rounded';
                div.setAttribute('data-recipient-type', 'user');
                div.setAttribute('data-recipient-value', user.id);
                div.innerHTML = `
                    <input type="checkbox" name="recipients[]" value="${user.id}" class="h-4 w-4 text-light-brown focus:ring-light-brown border-gray-300 rounded recipient-checkbox">
                    <label class="ml-3 text-sm text-gray-700 flex-1">${user.name}</label>
                    <span class="text-xs text-gray-500">(${user.email})</span>
                `;
                container.appendChild(div);
            });
        }

        console.log('About to setup controls');
        // Setup event handlers for the new functionality
        setupWizardRecipientControls();
        
        // Initial count update
        updateSelectedRecipientsCount();

    } catch (error) {
        console.error('Error loading recipients:', error);
        const container = document.getElementById('recipients-container');
        if (container) {
            container.innerHTML = '<div class="text-red-500 text-sm">Error loading recipients. Please try again.</div>';
        }
    }
}

// Function to refresh recipients data (called after changes in recipients modal)
function refreshWizardRecipients() {
    console.log('Refreshing wizard recipients...');
    // Small delay to ensure currentRecipientsData is updated
    setTimeout(() => {
        loadRecipientsForWizard();
    }, 100);
}

// Make this function globally available so it can be called from reports.js
window.refreshWizardRecipients = refreshWizardRecipients;

// Force refresh wizard recipients (debugging function)
function forceRefreshWizardRecipients() {
    console.log('Force refreshing wizard recipients...');
    console.log('Current window.currentRecipientsData:', window.currentRecipientsData);
    
    // Clear the shared data to force API reload
    window.currentRecipientsData = [];
    
    // Reload the wizard
    loadRecipientsForWizard();
}

// Helper function to reset/clear wizard recipients display
function resetWizardRecipientsDisplay() {
    console.log('Resetting wizard recipients display...');
    const container = document.getElementById('recipients-container');
    if (container) {
        container.innerHTML = '<div class="text-center py-4 text-gray-500">Loading recipients...</div>';
    }
}

// Make debugging function globally available
window.forceRefreshWizardRecipients = forceRefreshWizardRecipients;

// Setup controls for wizard recipient management
function setupWizardRecipientControls() {
    console.log('setupWizardRecipientControls called');
    const container = document.getElementById('recipients-container');
    if (!container) {
        console.error('Container not found in setupWizardRecipientControls');
        return;
    }

    console.log('Container found, setting up event listeners');

    // Use event delegation for all button clicks and checkbox changes
    container.addEventListener('click', function(e) {
        console.log('Container clicked, target:', e.target);
        
        // Handle Select All button
        if (e.target.id === 'wizard-select-all') {
            console.log('Select All clicked');
            const checkboxes = container.querySelectorAll('.recipient-checkbox');
            console.log('Found checkboxes:', checkboxes.length);
            checkboxes.forEach(checkbox => {
                checkbox.checked = true;
                // Trigger the change event to update visual feedback
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            });
        }
        
        // Handle Clear All button
        else if (e.target.id === 'wizard-clear-all') {
            console.log('Clear All clicked');
            const checkboxes = container.querySelectorAll('.recipient-checkbox');
            console.log('Found checkboxes:', checkboxes.length);
            checkboxes.forEach(checkbox => {
                checkbox.checked = false;
                // Trigger the change event to update visual feedback
                const event = new Event('change', { bubbles: true });
                checkbox.dispatchEvent(event);
            });
        }
        
        // Handle Refresh button
        else if (e.target.id === 'wizard-refresh' || e.target.closest('#wizard-refresh')) {
            console.log('Refresh clicked');
            // Show loading state
            const refreshBtn = document.getElementById('wizard-refresh');
            if (refreshBtn) {
                const originalHTML = refreshBtn.innerHTML;
                refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Refreshing...';
                refreshBtn.disabled = true;
                
                // Refresh the data
                refreshWizardRecipients();
                
                // Reset button after a delay
                setTimeout(() => {
                    if (refreshBtn) {
                        refreshBtn.innerHTML = originalHTML;
                        refreshBtn.disabled = false;
                    }
                }, 1000);
            }
        }
    });

    // Handle checkbox changes with event delegation
    container.addEventListener('change', function(e) {
        if (e.target.classList.contains('recipient-checkbox')) {
            console.log('Checkbox changed:', e.target.checked);
            triggerCheckboxChange(e.target);
        }
    });
    
    console.log('Event listeners set up successfully');
}

// Function to get selected recipients count and update UI
function updateSelectedRecipientsCount() {
    const container = document.getElementById('recipients-container');
    if (!container) return;

    const checkboxes = container.querySelectorAll('.recipient-checkbox');
    const selectedCount = container.querySelectorAll('.recipient-checkbox:checked').length;
    const totalCount = checkboxes.length;

    // Update the header text
    const headerSpan = container.querySelector('.text-sm.font-medium.text-gray-700');
    if (headerSpan) {
        headerSpan.textContent = `Select Recipients (${selectedCount}/${totalCount} selected)`;
    }

    console.log(`Selected: ${selectedCount}/${totalCount} recipients`);
}

// Helper function to handle checkbox visual feedback
function triggerCheckboxChange(checkbox) {
    const item = checkbox.closest('.recipient-item');
    if (item) {
        if (checkbox.checked) {
            item.classList.remove('opacity-50');
            item.classList.add('bg-blue-50', 'border-l-4', 'border-blue-400');
        } else {
            item.classList.add('opacity-50');
            item.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-400');
        }
    }
    
    // Update the count
    updateSelectedRecipientsCount();
}

// Add CSS styles for better visual feedback
if (!document.getElementById('wizard-recipient-styles')) {
    const style = document.createElement('style');
    style.id = 'wizard-recipient-styles';
    style.textContent = `
        .recipient-item {
            transition: all 0.2s ease;
            padding: 8px;
            border-radius: 4px;
            margin-bottom: 4px;
            border-left: 4px solid transparent;
        }
        .recipient-item:hover {
            background-color: #f9fafb;
        }
        .recipient-item.bg-blue-50 {
            background-color: #eff6ff;
            border-left-color: #60a5fa;
        }
        .recipient-item.opacity-50 {
            opacity: 0.5;
        }
    `;
    document.head.appendChild(style);
}

// Transform currentRecipientsData to the format expected by the wizard
function transformCurrentRecipientsData(currentData) {
    const users = [];
    
    console.log('transformCurrentRecipientsData called with:', currentData);
    
    if (!currentData || !Array.isArray(currentData)) {
        console.warn('Invalid currentData provided to transform function:', currentData);
        return { internal_roles: [], users: [], suppliers: [] };
    }
    
    currentData.forEach((recipient, index) => {
        console.log(`Processing recipient ${index}:`, recipient);
        
        if (recipient.type === 'user') {
            // Add to users array
            const userId = typeof recipient.id === 'string' ? recipient.id.replace('user_', '') : recipient.id;
            users.push({
                id: userId,
                name: recipient.name,
                email: recipient.email,
                role: recipient.role || recipient.department || 'User'
            });
            console.log('Added user:', users[users.length - 1]);
        } else if (recipient.type !== 'supplier') {
            // Log unknown types (excluding suppliers which are handled elsewhere)
            console.warn('Unknown recipient type:', recipient.type, 'for recipient:', recipient);
        }
    });
    
    const result = {
        internal_roles: [], // No longer using internal roles - focusing on individual users only
        users,
        suppliers: [] // Not used in wizard
    };
    
    console.log('Transform result:', result);
    return result;
}
