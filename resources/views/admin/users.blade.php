@extends('layouts.main-view') 

@section('content')
<div class="p-7 bg-light-background dark:bg-dark-background min-h-screen">
    <h1 class="text-3xl font-semibold text-coffee-brown dark:text-off-white mb-6">User Management</h1>
    <p class="text-soft-brown pb-10">Manage all users and vendor applications across your chain supply</p>

    @if (session('success'))
        <div class="bg-status-background-green border border-progress-bar-green text-status-text-green px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    @if (session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Info!</strong>
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please check the form below for errors.</span>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Tab Navigation -->
    <div class="bg-white dark:bg-warm-gray rounded-2xl shadow-md mb-6">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8 px-6">
                <button 
                    class="tab-button py-4 px-1 border-b-2 font-medium text-sm whitespace-nowrap border-light-brown text-light-brown"
                    data-tab="users-tab">
                    All Users
                </button>
                <button 
                    class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300 whitespace-nowrap"
                    data-tab="applications-tab">
                    Vendor Applications
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
            <!-- Users Tab -->
            <div id="users-tab" class="tab-content">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-dashboard-light dark:text-off-white">All Users</h2>
                    <button
                        class="bg-light-brown hover:bg-brown text-white font-bold py-2 px-4 rounded-md transition-colors duration-200"
                        data-modal-open="addUserModal"
                        data-mode="add">
                        Add New User
                    </button>
                </div>

                <div class="overflow-x-auto border-2 rounded-2xl p-4">
                    <table id="search-table" class="min-w-full leading-normal">
                <thead>
                    <tr>
                        <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider rounded-tl-lg">
                            Name
                        </th>
                        <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                            Role
                        </th>
                        <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider rounded-tr-lg">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors duration-150">
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $user->name }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $user->email }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                <span class="relative inline-block px-3 py-1 font-semibold leading-tight">
                                    <span aria-hidden="true" class="absolute inset-0 opacity-50 rounded-full
                                        @if($user->role === 'admin') bg-purple-200 text-purple-800
                                        @elseif($user->role === 'supplier') bg-green-200 text-green-800
                                        @elseif($user->role === 'vendor') bg-blue-200 text-blue-800
                                        @else bg-gray-200 text-gray-800
                                        @endif">
                                    </span>
                                    <span class="relative text-xs">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </span>
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm">
                                <div class="flex items-center space-x-3">
                                    {{-- Edit button --}}
                                    <button 
                                        type="button"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 text-xs cursor-pointer edit-user-btn"
                                        data-user-id="{{ $user->id }}"
                                        data-user-name="{{ $user->name }}"
                                        data-user-email="{{ $user->email }}"
                                        data-user-role="{{ $user->role }}"
                                        data-mode="edit">
                                        Edit
                                    </button>
                                    {{-- Delete button --}}
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline delete-user-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600 transition-colors duration-200 text-xs cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-center text-sm text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
                </div>
            </div>

            <!-- Vendor Applications Tab -->
            <div id="applications-tab" class="tab-content hidden">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-dashboard-light dark:text-off-white">Vendor Applications</h2>
                    <div class="flex space-x-2">
                        <select id="application-status-filter" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">
                            <option value="all">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="under_review">Under Review</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <div class="overflow-x-auto border-2 rounded-2xl p-4">
                    <table id="applications-table" class="min-w-full leading-normal">
                        <thead>
                            <tr>
                                <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider rounded-tl-lg">
                                    Application ID
                                </th>
                                <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                    Applicant
                                </th>
                                <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                    Business Name
                                </th>
                                <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-5 py-5 border-b-2 border-soft-gray dark:border-mild-gray bg-transparent dark:bg-dark-background text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider rounded-tr-lg">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody id="applications-tbody">
                            <!-- Applications will be loaded here via AJAX -->
                            <tr>
                                <td colspan="5" class="px-5 py-5 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex justify-center">
                                        <svg class="animate-spin h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span class="ml-2">Loading applications...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<x-modal 
    id="updateStatusModal" 
    title="Update Application Status" 
    size="md" 
    submit-form="updateStatusForm" 
    submit-text="Update Status"
    cancel-text="Cancel">
    
    <form id="updateStatusForm">
        @csrf
        <input type="hidden" id="application-id" name="application_id" value="">
        
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Application Status
            </label>
            <select id="status" name="status" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select status</option>
                <option value="pending">Pending</option>
                <option value="under_review">Under Review</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>

        <div class="mb-6" id="rejection-reason-field" style="display: none;">
            <label for="rejection_reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Rejection Reason
            </label>
            <textarea id="rejection_reason" name="rejection_reason" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                      placeholder="Please provide a reason for rejection"></textarea>
        </div>
    </form>
</x-modal>

<!-- Vendor Details Modal -->
<x-modal 
    id="vendorDetailsModal" 
    title="Vendor Application Details" 
    size="2xl"
    :showFooter="false">
    
    <div id="vendor-details-content" class="space-y-6">
        <!-- Application Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-off-white mb-4">Application Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Application ID</label>
                    <p id="detail-application-id" class="text-sm text-gray-900 dark:text-off-white font-mono"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                    <p id="detail-status" class="text-sm"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Submitted On</label>
                    <p id="detail-submitted-date" class="text-sm text-gray-900 dark:text-off-white"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Validated On</label>
                    <p id="detail-validated-date" class="text-sm text-gray-900 dark:text-off-white"></p>
                </div>
            </div>
        </div>

        <!-- Applicant Information -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-off-white mb-4">Applicant Information</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name</label>
                    <p id="detail-applicant-name" class="text-sm text-gray-900 dark:text-off-white"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Name</label>
                    <p id="detail-business-name" class="text-sm text-gray-900 dark:text-off-white"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                    <p id="detail-email" class="text-sm text-gray-900 dark:text-off-white"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number</label>
                    <p id="detail-phone" class="text-sm text-gray-900 dark:text-off-white"></p>
                </div>
            </div>
        </div>

        <!-- Documents -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-off-white mb-4">Submitted Documents</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bank Statement</label>
                    <div id="detail-bank-statement" class="text-sm">
                        <!-- Document link will be populated here -->
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Trading License</label>
                    <div id="detail-trading-license" class="text-sm">
                        <!-- Document link will be populated here -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Validation Details -->
        <div class="mb-6" id="validation-details-section" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-off-white mb-4">Validation Details</h3>
            <div id="detail-validation-message" class="bg-gray-50 dark:bg-gray-800 p-3 rounded-md">
                <!-- Validation message will be populated here -->
            </div>
        </div>

        <!-- Visit Information -->
        <div class="mb-6" id="visit-details-section" style="display: none;">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-off-white mb-4">Visit Information</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Scheduled Visit Date</label>
                <p id="detail-visit-date" class="text-sm text-gray-900 dark:text-off-white"></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex space-x-2">
                <button id="update-status-btn" onclick="openUpdateStatusModalFromDetails()" 
                        class="bg-light-brown hover:bg-brown text-white px-4 py-2 rounded text-sm">
                    Update Status
                </button>
                <button id="download-docs-btn" onclick="downloadAllDocuments()" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded text-sm">
                    Download Documents
                </button>
            </div>
        </div>
    </div>
</x-modal>

{{-- Add/Edit User Modal --}}
<x-modal 
    id="addUserModal" 
    title="Add New User" 
    size="md" 
    submit-form="addUserForm" 
    submit-text="Add User"
    cancel-text="Cancel">
    
    <form action="{{ route('admin.users.store') }}" method="POST" id="addUserForm">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="">
        
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Full Name
            </label>
            <input type="text" id="name" name="name" required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter full name" value="{{ old('name') }}">
        </div>

        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email Address
            </label>
            <input type="email" id="email" name="email" required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter email address" value="{{ old('email') }}">
        </div>

        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Role
            </label>
            <select id="role" name="role" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select a role</option>
                <option value="admin">Admin</option>
                <option value="supplier">Supplier</option>
                <option value="vendor">Vendor</option>
            </select>
        </div>

        <div class="mb-4" id="password-field">
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password <span id="password-required-text">(Required)</span><span id="password-optional-text" style="display: none;">(Leave blank to keep current password)</span>
            </label>
            <input type="password" id="password" name="password" minlength="8"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter password (min. 8 characters)">
        </div>

        <div class="mb-6" id="password-confirmation-field">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Confirm Password
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" minlength="8"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Confirm password">
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
    let applicationsDataTable, approvedVendorsDataTable;

    // Tab switching functionality
    document.addEventListener('DOMContentLoaded', function () {
        console.log("User management script initialized.");

        // Initialize tabs
        initializeTabs();
        
        // Initialize user management functionality
        initializeUserManagement();
        
        // Initialize vendor application functionality
        initializeVendorApplications();
        
        // Load initial data
        loadVendorApplications();
    });

    function initializeTabs() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = button.getAttribute('data-tab');
                
                // Update button states
                tabButtons.forEach(btn => {
                    btn.classList.remove('border-light-brown', 'text-light-brown');
                    btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                });
                
                button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
                button.classList.add('border-light-brown', 'text-light-brown');
                
                // Update content visibility
                tabContents.forEach(content => {
                    content.classList.add('hidden');
                });
                
                document.getElementById(targetTab).classList.remove('hidden');
                
                // Load data for specific tabs
                if (targetTab === 'applications-tab') {
                    loadVendorApplications();
                }
            });
        });
    }

    function initializeUserManagement() {
        // Initialize DataTable for users
        if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
            const dataTable = new simpleDatatables.DataTable("#search-table", {
                searchable: true,
                sortable: false
            });
        }

        // --- Essential Modal and Form Elements ---
        const modal = document.getElementById('addUserModal');
        const form = document.getElementById('addUserForm');

        if (!modal || !form) {
            console.error("Fatal Error: The modal or its form is missing from the DOM.");
            return;
        }

        const modalTitle = modal.querySelector('h3');
        const submitButton = modal.querySelector('button[type="submit"]');
        const methodInput = form.querySelector('input[name="_method"]');
        const passwordField = document.getElementById('password-field');
        const passwordRequiredText = document.getElementById('password-required-text');
        const passwordOptionalText = document.getElementById('password-optional-text');

        // Check if all elements were found.
        if (!modalTitle || !submitButton || !methodInput || !passwordField) {
            console.error("Initialization failed: A required element could not be found.");
            return;
        }

        // --- Add New User Button ---
        const addUserButton = document.querySelector('button[data-modal-open="addUserModal"]');
        if (addUserButton) {
            addUserButton.addEventListener('click', function() {
                console.log("Add New User button clicked.");
                form.reset();
                modalTitle.textContent = 'Add New User';
                submitButton.textContent = 'Add User';
                form.action = "{{ route('admin.users.store') }}";
                methodInput.value = 'POST';

                passwordField.style.display = 'block';
                document.getElementById('password').required = true;
                document.getElementById('password_confirmation').required = true;
                passwordRequiredText.style.display = 'inline';
                passwordOptionalText.style.display = 'none';
            });
        }

        // --- Edit User Buttons ---
        const editButtons = document.querySelectorAll('.edit-user-btn');

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const userId = btn.getAttribute('data-user-id');
                const userName = btn.getAttribute('data-user-name');
                const userEmail = btn.getAttribute('data-user-email');
                const userRole = btn.getAttribute('data-user-role');
                

                // Verify we have the user data
                if (!userId || !userName || !userEmail || !userRole) {
                    console.error("Missing user data:", { userId, userName, userEmail, userRole });
                    alert("Error: Missing user data. Please refresh the page and try again.");
                    return;
                }

                form.reset();
                modalTitle.textContent = 'Edit User';
                submitButton.textContent = 'Update User';
                
                // Build the update URL manually to ensure proper encoding
                const updateUrl = "{{ route('admin.users.update', ['user' => ':id']) }}".replace(':id', encodeURIComponent(userId));
                form.action = updateUrl;
                methodInput.value = 'PATCH';

                // Populate form fields
                document.getElementById('name').value = userName;
                document.getElementById('email').value = userEmail;
                document.getElementById('role').value = userRole;

                // Password is optional for updates
                document.getElementById('password').required = false;
                document.getElementById('password_confirmation').required = false;
                passwordRequiredText.style.display = 'none';
                passwordOptionalText.style.display = 'inline';
                            
                // Open the modal manually
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';
                
                // Focus on the name field
                setTimeout(() => {
                    document.getElementById('name').focus();
                }, 100);
            });
        });

        // --- Delete User Confirmation ---
        const deleteForms = document.querySelectorAll('.delete-user-form');
        deleteForms.forEach(deleteForm => {
            deleteForm.addEventListener('submit', function(e) {
                if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }

    function initializeVendorApplications() {
        // Status filter for applications
        const statusFilter = document.getElementById('application-status-filter');
        if (statusFilter) {
            statusFilter.addEventListener('change', function() {
                loadVendorApplications();
            });
        }

        // Status change handling
        document.getElementById('status').addEventListener('change', function() {
            const rejectionField = document.getElementById('rejection-reason-field');
            const rejectionTextarea = document.getElementById('rejection_reason');
            if (this.value === 'rejected') {
                rejectionField.style.display = 'block';
                rejectionTextarea.required = true;
                // Clear any previous value to ensure fresh input
                rejectionTextarea.value = '';
            } else {
                rejectionField.style.display = 'none';
                rejectionTextarea.required = false;
                // Clear the value when not rejecting
                rejectionTextarea.value = '';
            }
        });

        // Update Status Form Submission
        const updateStatusForm = document.getElementById('updateStatusForm');
        updateStatusForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateApplicationStatus();
        });
    }

    async function loadVendorApplications() {
        const tbody = document.getElementById('applications-tbody');
        const statusFilter = document.getElementById('application-status-filter').value;
        
        try {
            // Show loading
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-5 py-5 text-center text-gray-500">
                        <div class="flex justify-center items-center">
                            <svg class="animate-spin h-5 w-5 text-gray-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading applications...
                        </div>
                    </td>
                </tr>
            `;

            const url = new URL('/api/vendor-applications', window.location.origin);
            if (statusFilter !== 'all') {
                url.searchParams.append('status', statusFilter);
            }

            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load applications');
            }

            const data = await response.json();
            displayApplications(data.applications || []);

        } catch (error) {
            console.error('Error loading applications:', error);
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-5 py-5 text-center text-red-500">
                        Error loading applications. Please refresh the page.
                    </td>
                </tr>
            `;
        }
    }

    function displayApplications(applications) {
        const tbody = document.getElementById('applications-tbody');
        
        if (applications.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="px-5 py-5 text-center text-gray-500">
                        No applications found.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = applications.map(app => `
            <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors">
                <td class="px-5 py-5 border-b border-soft-gray text-sm font-mono">${app.id}</td>
                <td class="px-5 py-5 border-b border-soft-gray text-sm">${app.applicant_name}</td>
                <td class="px-5 py-5 border-b border-soft-gray text-sm">${app.business_name}</td>
                <td class="px-5 py-5 border-b border-soft-gray text-sm">
                    <div class="flex flex-col space-y-1">
                        <span class="px-2 py-1 text-xs rounded-full text-center ${getStatusBadgeClass(app.status)}">
                            ${getStatusLabel(app.status)}
                        </span>
                        ${app.status === 'pending' && app.validated_at && app.validation_message ? `
                            <span class="px-2 py-1 text-xs rounded-full text-center ${getValidationStatusBadgeClass(app.status, app.validation_message)}">
                                ${getValidationStatusLabel(app.status, app.validation_message)}
                            </span>
                        ` : ''}
                    </div>
                </td>
                <td class="px-5 py-5 border-b border-soft-gray text-sm">
                    <button onclick="openUpdateStatusModal('${app.id}', '${app.status}')" 
                            class="text-blue-600 hover:text-blue-900 text-xs mr-3">
                        Update Status
                    </button>
                    <button onclick="viewApplicationDetails('${app.id}')" 
                            class="text-green-600 hover:text-green-900 text-xs">
                        View Details
                    </button>
                    ${app.status === 'pending' && app.validated_at && app.validation_message && 
                      (app.validation_message.includes('Failed to communicate') || 
                       app.validation_message.includes('validation_failed') ||
                       app.validation_message.includes('error')) ? `
                        <button onclick="retryValidation('${app.id}')" 
                                class="text-purple-600 hover:text-purple-900 text-xs ml-3">
                            Retry Validation
                        </button>
                    ` : ''}
                </td>
            </tr>
        `).join('');
    }

    function openUpdateStatusModal(applicationId, currentStatus) {
        document.getElementById('application-id').value = applicationId;
        document.getElementById('status').value = currentStatus;
        
        // Always clear the rejection reason when opening modal
        document.getElementById('rejection_reason').value = '';
        
        // Handle rejection reason field visibility
        const rejectionField = document.getElementById('rejection-reason-field');
        const rejectionTextarea = document.getElementById('rejection_reason');
        if (currentStatus === 'rejected') {
            rejectionField.style.display = 'block';
            rejectionTextarea.required = true;
        } else {
            rejectionField.style.display = 'none';
            rejectionTextarea.required = false;
        }
        
        document.getElementById('updateStatusModal').classList.remove('hidden');
        document.getElementById('updateStatusModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    async function updateApplicationStatus() {
        const form = document.getElementById('updateStatusForm');
        const formData = new FormData(form);
        const applicationId = document.getElementById('application-id').value;
        const status = document.getElementById('status').value;
        
        try {
            const url = status === 'rejected' 
                ? `/admin/vendor-applications/${applicationId}/reject`
                : `/admin/vendor-applications/${applicationId}/update-status`;
                
            const response = await fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                document.getElementById('updateStatusModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                
                // Show appropriate message
                if (data.warning) {
                    showNotification(data.message + ' Note: ' + data.warning, 'warning');
                } else {
                    showNotification(data.message || 'Status updated successfully', 'success');
                }
                
                // Reload applications
                loadVendorApplications();
            } else {
                showNotification(data.message || 'Failed to update status', 'error');
            }

        } catch (error) {
            console.error('Error updating status:', error);
            showNotification('An error occurred while updating the status', 'error');
        }
    }

    function generateRandomPassword() {
        const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let password = "";
        for (let i = 0; i < 12; i++) {
            password += charset.charAt(Math.floor(Math.random() * charset.length));
        }
        return password;
    }

    function getStatusBadgeClass(status) {
        const classes = {
            'pending': 'bg-yellow-100 text-yellow-800',
            'under_review': 'bg-blue-100 text-blue-800',
            'approved': 'bg-green-100 text-green-800',
            'rejected': 'bg-red-100 text-red-800'
        };
        return classes[status] || 'bg-gray-100 text-gray-800';
    }

    function getStatusLabel(status) {
        const labels = {
            'pending': 'Pending',
            'under_review': 'Under Review',
            'approved': 'Approved',
            'rejected': 'Rejected'
        };
        return labels[status] || 'Unknown';
    }

    function getValidationStatusBadgeClass(status, validationMessage) {
        // If status is pending and there's a validation message, it likely means validation failed
        if (status === 'pending' && validationMessage && 
            (validationMessage.includes('Failed to communicate') || 
             validationMessage.includes('validation_failed') ||
             validationMessage.includes('error'))) {
            return 'bg-red-100 text-red-800'; // Validation failed
        }
        
        // If status is pending without validation message, it's truly pending
        if (status === 'pending') {
            return 'bg-yellow-100 text-yellow-800'; // Awaiting validation
        }
        
        // Use standard status colors for other cases
        return getStatusBadgeClass(status);
    }

    function getValidationStatusLabel(status, validationMessage) {
        // If status is pending and there's a validation message indicating failure
        if (status === 'pending' && validationMessage && 
            (validationMessage.includes('Failed to communicate') || 
             validationMessage.includes('validation_failed') ||
             validationMessage.includes('error'))) {
            return 'Validation Failed';
        }
        
        // If status is pending without validation message, it's truly pending
        if (status === 'pending') {
            return 'Awaiting Validation';
        }
        
        return getStatusLabel(status);
    }

    async function viewApplicationDetails(applicationId) {
        try {
            // Show loading in modal
            const modal = document.getElementById('vendorDetailsModal');
            const content = document.getElementById('vendor-details-content');
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            
            content.innerHTML = `
                <div class="flex justify-center items-center py-8">
                    <svg class="animate-spin h-8 w-8 text-gray-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Loading application details...
                </div>
            `;

            const response = await fetch(`/admin/vendor-applications/${applicationId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load application details');
            }

            const data = await response.json();
            displayApplicationDetails(data.application);

        } catch (error) {
            console.error('Error loading application details:', error);
            const content = document.getElementById('vendor-details-content');
            content.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    Error loading application details. Please try again.
                </div>
            `;
        }
    }

    function displayApplicationDetails(app) {
        // Reset the content to original structure
        const content = document.getElementById('vendor-details-content');
        content.innerHTML = `
            <!-- Application Information -->
            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                <h3 class="text-base font-semibold text-gray-900 dark:text-off-white mb-3">Application Information</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Application ID</label>
                        <p class="text-gray-900 dark:text-off-white font-mono">${app.id}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <span class="px-2 py-1 text-xs rounded-full text-center ${getStatusBadgeClass(app.status)}">
                            ${getStatusLabel(app.status)}
                        </span>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Submitted On</label>
                        <p class="text-gray-900 dark:text-off-white">${formatDate(app.created_at)}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Validated On</label>
                        <p class="text-gray-900 dark:text-off-white">${app.validated_at ? formatDate(app.validated_at) : 'Not validated'}</p>
                    </div>
                </div>
            </div>

            <!-- Applicant Information -->
            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                <h3 class="text-base font-semibold text-gray-900 dark:text-off-white mb-3">Applicant Information</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                        <p class="text-gray-900 dark:text-off-white">${app.applicant_name}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Business Name</label>
                        <p class="text-gray-900 dark:text-off-white">${app.business_name}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <p class="text-gray-900 dark:text-off-white break-all">${app.email}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                        <p class="text-gray-900 dark:text-off-white">${app.phone_number}</p>
                    </div>
                </div>
            </div>

            <!-- Documents -->
            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                <h3 class="text-base font-semibold text-gray-900 dark:text-off-white mb-3">Submitted Documents</h3>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bank Statement</label>
                        <div>
                            ${app.bank_statement_path ? 
                                `<a href="/admin/vendor-applications/${app.id}/download/bank-statement" 
                                   class="text-blue-600 hover:text-blue-800 flex items-center text-xs">
                                   <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                       <path d="M4 18h12V6h-4V2H4v16zm-2 1V1h10l4 4v14H2z"/>
                                   </svg>
                                   Download
                                 </a>` : 
                                '<span class="text-gray-500 text-xs">Not uploaded</span>'
                            }
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Trading License</label>
                        <div>
                            ${app.trading_license_path ? 
                                `<a href="/admin/vendor-applications/${app.id}/download/trading-license" 
                                   class="text-blue-600 hover:text-blue-800 flex items-center text-xs">
                                   <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                       <path d="M4 18h12V6h-4V2H4v16zm-2 1V1h10l4 4v14H2z"/>
                                   </svg>
                                   Download
                                 </a>` : 
                                '<span class="text-gray-500 text-xs">Not uploaded</span>'
                            }
                        </div>
                    </div>
                </div>
            </div>

            <!-- Validation Details -->
            ${app.validation_message ? `
                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-off-white mb-3">Validation Details</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Validation Status</label>
                            <span class="px-2 py-1 text-xs rounded-full text-center ${getValidationStatusBadgeClass(app.status, app.validation_message)}">
                                ${getValidationStatusLabel(app.status, app.validation_message)}
                            </span>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Message</label>
                            <p class="text-sm text-gray-900 dark:text-off-white p-2 bg-white dark:bg-gray-700 rounded border break-words">${app.validation_message}</p>
                        </div>
                        ${app.validated_at ? `
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Validated At</label>
                                <p class="text-sm text-gray-900 dark:text-off-white">${formatDate(app.validated_at)}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            ` : ''}

            <!-- Visit Information -->
            ${app.visit_scheduled ? `
                <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-off-white mb-3">Visit Information</h3>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Scheduled Visit Date</label>
                        <p class="text-sm text-gray-900 dark:text-off-white">${formatDate(app.visit_scheduled)}</p>
                    </div>
                </div>
            ` : ''}

            <!-- Action Buttons -->
            <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg">
                <div class="flex justify-between items-center">
                    <div class="flex space-x-2">
                        <button onclick="openUpdateStatusModalFromDetails('${app.id}', '${app.status}')" 
                                class="bg-light-brown hover:bg-brown text-white px-3 py-2 rounded text-sm">
                            Update Status
                        </button>
                    </div>
                    <button onclick="closeVendorDetailsModal()" 
                            class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500">
                        Close
                    </button>
                </div>
            </div>
        `;
    }

    function openUpdateStatusModalFromDetails(applicationId, currentStatus) {
        // Close details modal first
        closeVendorDetailsModal();
        
        // Open update status modal
        openUpdateStatusModal(applicationId, currentStatus);
    }

    function closeVendorDetailsModal() {
        document.getElementById('vendorDetailsModal').classList.add('hidden');
        document.getElementById('vendorDetailsModal').classList.remove('flex');
        document.body.style.overflow = 'auto';
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function viewApplication(applicationId) {
        window.open(`/admin/vendor-applications/${applicationId}`, '_blank');
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-lg ${
            type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' :
            type === 'error' ? 'bg-red-100 border border-red-400 text-red-700' :
            type === 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' :
            'bg-blue-100 border border-blue-400 text-blue-700'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove after 7 seconds for warnings (longer than other notifications)
        setTimeout(() => {
            notification.remove();
        }, type === 'warning' ? 7000 : 5000);
    }

    async function retryValidation(applicationId) {
        try {
            showNotification('Retrying validation...', 'info');

            const response = await fetch(`/admin/vendor-applications/${applicationId}/retry-validation`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showNotification(data.message || 'Validation retry initiated successfully', 'success');
                // Reload applications to show updated status
                loadVendorApplications();
            } else {
                showNotification(data.message || 'Failed to retry validation', 'error');
            }

        } catch (error) {
            console.error('Error retrying validation:', error);
            showNotification('An error occurred while retrying validation', 'error');
        }
    }
</script>
@endpush
