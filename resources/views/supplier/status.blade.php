@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-white">
    <!-- Left side - Image -->
    <img src="{{ asset('/warehouse.image.webp') }}" alt="warehouse" class="w-1/2 h-full object-cover sticky top-0 left-0">

    <!-- Right side - Status Check -->
    <div class="w-1/2 flex items-start justify-center">
        <div class="w-full flex flex-col items-left">
            <div class="flex items-center justify-end border-b h-17 pr-3">
                <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-8 h-8">
                <h1 class="text-coffee-brown text-2xl font-semibold ml-2">BeanTrack</h1>
            </div>
        
            <!-- Header -->
            <div class="text-center mb-8 w-full pt-5 pl-10 pr-10">
                <h1 class="text-4xl font-semibold text-light-brown mb-3">Check Application Status</h1>
                <p class="text-brown">Track your supplier application progress</p>
            </div>
            
            <!-- Status Check Form -->
            <div class="pl-10 pr-10">
                <form id="statusCheckForm" class="space-y-6 mb-8">
                    <div>
                        <label for="status_token" class="block mb-2 text-coffee-brown font-semibold">Status Token *</label>
                        <input type="text" id="status_token" name="status_token" required 
                            placeholder="Enter your 32-character status token"
                            class="w-full border-soft-gray rounded border-2 h-12 px-3 focus:border-light-brown focus:outline-none transition-colors font-mono">
                        <p class="text-sm text-gray-600 mt-1">This token was provided when you submitted your application</p>
                    </div>

                    <div class="flex justify-center">
                        <button type="submit" id="checkStatusButton" 
                            class="w-full bg-coffee-brown hover:bg-hover-brown text-white font-semibold py-3 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="checkStatusButtonText">Check Status</span>
                            <div id="loadingSpinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
                        </button>
                    </div>
                </form>

                <!-- Error Messages -->
                <div id="errorMessages" class="hidden bg-status-background-red border border-red-300 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p id="errorMessage"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Application Status -->
                <div id="statusResult" class="hidden bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                    <h3 class="text-xl font-semibold text-coffee-brown mb-4">Application Status</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Application ID:</span>
                            <span id="applicationId" class="font-mono text-coffee-brown"></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Applicant Name:</span>
                            <span id="applicantName"></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Business Name:</span>
                            <span id="businessName"></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Status:</span>
                            <span id="applicationStatus" class="px-3 py-1 rounded-full text-sm font-medium"></span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-700">Submitted:</span>
                            <span id="submittedAt"></span>
                        </div>
                        
                        <div id="validatedAtRow" class="justify-between items-center hidden">
                            <span class="font-medium text-gray-700">Validated:</span>
                            <span id="validatedAt"></span>
                        </div>
                        
                        <div id="visitScheduledRow" class="justify-between items-center hidden">
                            <span class="font-medium text-gray-700">Visit Scheduled:</span>
                            <span id="visitScheduled"></span>
                        </div>
                        
                        <div id="validationMessageRow" class="hidden">
                            <div class="pt-3 border-t">
                                <span class="font-medium text-gray-700">Message:</span>
                                <p id="validationMessage" class="mt-2 text-sm text-gray-600 bg-gray-50 p-3 rounded"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="text-center pt-6 border-t mt-8">
                    <p class="text-sm text-gray-600 mb-4">Need help or want to apply?</p>
                    <div class="space-y-2">
                        <a href="{{ route('supplier.apply') }}" class="block text-coffee-brown hover:text-hover-brown font-semibold">Apply as Supplier</a>
                        <a href="{{ route('supplier.onboarding') }}" class="block text-coffee-brown hover:text-hover-brown font-semibold">Back to Supplier Portal</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statusCheckForm');
    const checkStatusButton = document.getElementById('checkStatusButton');
    const checkStatusButtonText = document.getElementById('checkStatusButtonText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const errorMessages = document.getElementById('errorMessages');
    const errorMessage = document.getElementById('errorMessage');
    const statusResult = document.getElementById('statusResult');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Reset UI
        hideMessages();
        setLoadingState(true);
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("supplier.application.status") }}', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const url = new URL(response.url);
            url.searchParams.append('token', formData.get('status_token'));
            
            const statusResponse = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await statusResponse.json();
            
            if (data.success) {
                showStatusResult(data.data);
            } else {
                showErrorMessage(data.message || 'Application not found');
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorMessage('An unexpected error occurred. Please try again.');
        } finally {
            setLoadingState(false);
        }
    });

    function setLoadingState(loading) {
        checkStatusButton.disabled = loading;
        checkStatusButtonText.textContent = loading ? 'Checking...' : 'Check Status';
        loadingSpinner.classList.toggle('hidden', !loading);
    }

    function hideMessages() {
        errorMessages.classList.add('hidden');
        statusResult.classList.add('hidden');
    }

    function showErrorMessage(message) {
        errorMessage.textContent = message;
        errorMessages.classList.remove('hidden');
        errorMessages.scrollIntoView({ behavior: 'smooth' });
    }

    function showStatusResult(data) {
        // Populate the status result
        document.getElementById('applicationId').textContent = data.application_id;
        document.getElementById('applicantName').textContent = data.applicant_name;
        document.getElementById('businessName').textContent = data.business_name;
        document.getElementById('submittedAt').textContent = new Date(data.submitted_at).toLocaleDateString();
        
        // Set status with appropriate styling
        const statusElement = document.getElementById('applicationStatus');
        statusElement.textContent = data.status.replace('_', ' ').toUpperCase();
        statusElement.className = `px-3 py-1 rounded-full text-sm font-medium ${getStatusClasses(data.status)}`;
        
        // Show conditional fields
        if (data.validated_at) {
            document.getElementById('validatedAt').textContent = new Date(data.validated_at).toLocaleDateString();
            const validatedAtRow = document.getElementById('validatedAtRow');
            validatedAtRow.classList.remove('hidden');
            validatedAtRow.classList.add('flex');
        }
        
        if (data.visit_scheduled) {
            document.getElementById('visitScheduled').textContent = new Date(data.visit_scheduled).toLocaleDateString();
            const visitScheduledRow = document.getElementById('visitScheduledRow');
            visitScheduledRow.classList.remove('hidden');
            visitScheduledRow.classList.add('flex');
        }
        
        if (data.validation_message) {
            document.getElementById('validationMessage').textContent = data.validation_message;
            document.getElementById('validationMessageRow').classList.remove('hidden');
        }
        
        statusResult.classList.remove('hidden');
        statusResult.scrollIntoView({ behavior: 'smooth' });
    }

    function getStatusClasses(status) {
        switch(status) {
            case 'pending':
                return 'bg-yellow-100 text-yellow-800';
            case 'under_review':
                return 'bg-blue-100 text-blue-800';
            case 'approved':
                return 'bg-green-100 text-green-800';
            case 'rejected':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
});
</script>
@endsection
