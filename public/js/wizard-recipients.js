// Load recipients dynamically for the wizard
async function loadRecipientsForWizard() {
    try {
        const response = await fetch('/reports/recipients', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();
        const container = document.getElementById('recipients-container');
        
        if (!container) return;

        // Clear loading message
        container.innerHTML = '';

        // Add internal roles (departments)
        if (data.internal_roles && data.internal_roles.length > 0) {
            data.internal_roles.forEach(role => {
                const div = document.createElement('div');
                div.className = 'flex items-center';
                div.innerHTML = `
                    <input type="checkbox" name="recipients[]" value="${role}" class="h-4 w-4 text-light-brown focus:ring-light-brown border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">${role}</label>
                `;
                container.appendChild(div);
            });
        }

        // Add individual users if any
        if (data.users && data.users.length > 0) {
            // Add a separator
            const separator = document.createElement('div');
            separator.className = 'border-t border-gray-200 my-3';
            container.appendChild(separator);

            const header = document.createElement('h5');
            header.className = 'text-sm font-medium text-gray-700 mb-2';
            header.textContent = 'Individual Users';
            container.appendChild(header);

            data.users.forEach(user => {
                const div = document.createElement('div');
                div.className = 'flex items-center';
                div.innerHTML = `
                    <input type="checkbox" name="recipients[]" value="${user.id}" class="h-4 w-4 text-light-brown focus:ring-light-brown border-gray-300 rounded">
                    <label class="ml-2 text-sm text-gray-700">${user.name} (${user.email})</label>
                `;
                container.appendChild(div);
            });
        }

    } catch (error) {
        console.error('Error loading recipients:', error);
        const container = document.getElementById('recipients-container');
        if (container) {
            container.innerHTML = '<div class="text-red-500 text-sm">Error loading recipients. Please try again.</div>';
        }
    }
}
