@extends('layouts.main-view') 

@section('content')
<div class="p-7 bg-light-background dark:bg-dark-background min-h-screen">
    <h1 class="text-3xl font-semibold text-coffee-brown dark:text-off-white mb-6">User Management</h1>
    <p class="text-soft-brown pb-10">Manage all users across your chain supply</p>

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

    <div class="bg-white dark:bg-warm-gray rounded-2xl shadow-md p-6 mb-6">
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
</div>

{{-- Add/Edit User Modal using the reusable component --}}
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
        
        {{-- Name Field --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Full Name
            </label>
            <input type="text" 
                   id="name" 
                   name="name" 
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter full name"
                   value="{{ old('name') }}">
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email Field --}}
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Email Address
            </label>
            <input type="email" 
                   id="email" 
                   name="email" 
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter email address"
                   value="{{ old('email') }}">
            @error('email')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Role Field --}}
        <div class="mb-4">
            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Role
            </label>
            <select id="role" 
                    name="role" 
                    required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select a role</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="supplier" {{ old('role') === 'supplier' ? 'selected' : '' }}>Supplier</option>
                <option value="vendor" {{ old('role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
            </select>
            @error('role')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password Field --}}
        <div class="mb-4" id="password-field">
            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Password <span id="password-required-text">(Required)</span><span id="password-optional-text" style="display: none;">(Leave blank to keep current password)</span>
            </label>
            <input type="password" 
                   id="password" 
                   name="password" 
                   minlength="8"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter password (min. 8 characters)">
            @error('password')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Confirm Password Field --}}
        <div class="mb-6" id="password-confirmation-field">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Confirm Password
            </label>
            <input type="password" 
                   id="password_confirmation" 
                   name="password_confirmation" 
                   minlength="8"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Confirm password">
        </div>
    </form>
</x-modal>
@endsection

@push('scripts')
<script>
    if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
    const dataTable = new simpleDatatables.DataTable("#search-table", {
        searchable: true,
        sortable: false
    });
    }

    document.addEventListener('DOMContentLoaded', function () {
    console.log("User management script initialized.");

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
    const addUserButton = document.querySelector('button[data-mode="add"]');
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
                        
            // Open the modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
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

});
</script>
@endpush
