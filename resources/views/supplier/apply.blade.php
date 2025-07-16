@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-white">
    <!-- Left side - Image -->
    <img src="{{ asset('/warehouse.image.webp') }}" alt="warehouse" class="w-1/2 h-full object-cover sticky top-0 left-0">

        <!-- Right side - Form -->
        <div class="w-1/2 flex items-start justify-center">
                <div class="w-full flex flex-col items-left">
                    <div class="flex items-center justify-end border-b h-17 pr-3">
                        <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-8 h-8">
                        <h1 class="text-coffee-brown text-2xl font-semibold ml-2">BeanTrack</h1>
                    </div>
                
                    <!-- Header -->
                    <div class="text-center mb-8 w-full pt-5 pl-10 pr-10">
                        <h1 class="text-4xl font-semibold text-light-brown mb-3">Supplier Application</h1>
                        <p class="text-brown">Submit your application to become a BeanTrack supplier</p>
                    </div>
                

                    <!-- Application Form -->
                    <form id="supplierApplicationForm" class="space-y-6 pl-10 pr-10">
                        @csrf
                        
                        <!-- Applicant Information -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-coffee-brown">Personal Information</h3>
                            
                            <div>
                                <label for="applicant_name" class="block mb-2 text-coffee-brown font-semibold">Full Name *</label>
                                <input type="text" id="applicant_name" name="applicant_name" required 
                                    class="w-full border-soft-gray rounded border-2 h-12 px-3 focus:border-light-brown focus:outline-none transition-colors">
                            </div>

                            <div>
                                <label for="email" class="block mb-2 text-coffee-brown font-semibold">Email Address *</label>
                                <input type="email" id="email" name="email" required 
                                    class="w-full border-soft-gray rounded border-2 h-12 px-3 focus:border-light-brown focus:outline-none transition-colors">
                            </div>

                            <div>
                                <label for="phone_number" class="block mb-2 text-coffee-brown font-semibold">Phone Number *</label>
                                <input type="tel" id="phone_number" name="phone_number" required 
                                    class="w-full border-soft-gray rounded border-2 h-12 px-3 focus:border-light-brown focus:outline-none transition-colors">
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-coffee-brown">Business Information</h3>
                            
                            <div>
                                <label for="business_name" class="block mb-2 text-coffee-brown font-semibold">Business Name *</label>
                                <input type="text" id="business_name" name="business_name" required 
                                    class="w-full border-soft-gray rounded border-2 h-12 px-3 focus:border-light-brown focus:outline-none transition-colors">
                            </div>
                        </div>

                        <!-- Document Uploads -->
                        <div class="space-y-4">
                            <h3 class="text-xl font-semibold text-coffee-brown">Required Documents</h3>
                            
                            <div>
                                <label for="bank_statement" class="block mb-2 text-coffee-brown font-semibold">Bank Statement (PDF) *</label>
                                <div class="relative">
                                    <input type="file" id="bank_statement" name="bank_statement" accept=".pdf" required 
                                        class="w-full border-soft-gray rounded border-2 h-10 px-3 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-light-gray file:text-coffee-brown hover:file:bg-soft-gray">
                                    <div class="mt-2 p-3 bg-status-background-blue rounded-lg border border-blue-200">
                                        <p class="text-sm text-status-text-blue font-medium mb-1">📋 Document Requirements:</p>
                                        <ul class="text-xs text-status-text-blue space-y-1">
                                            <li>• File must contain "bank-statement" in the filename</li>
                                            <li>• PDF format only, maximum 10MB</li>
                                            <li>• Must be recent (within last 3 months)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="trading_license" class="block mb-2 text-coffee-brown font-semibold">Trading License (PDF) *</label>
                                <div class="relative">
                                    <input type="file" id="trading_license" name="trading_license" accept=".pdf" required 
                                        class="w-full border-soft-gray rounded border-2 h-10 px-3 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-light-gray file:text-coffee-brown hover:file:bg-soft-gray">
                                    <div class="mt-2 p-3 bg-status-background-blue rounded-lg border border-blue-200">
                                        <p class="text-sm text-status-text-blue font-medium mb-1">📋 Document Requirements:</p>
                                        <ul class="text-xs text-status-text-blue space-y-1">
                                            <li>• File must contain "trading-license" in the filename</li>
                                            <li>• PDF format only, maximum 10MB</li>
                                            <li>• Must be valid and current</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Error Messages -->
                        <div id="errorMessages" class="hidden bg-status-background-red border border-red-300 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Application Errors</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul id="errorList" class="list-disc list-inside space-y-1"></ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Success Message -->
                        <div id="successMessage" class="hidden bg-status-background-green border border-green-300 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">Application Submitted Successfully!</h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Your supplier application has been submitted for review. You will receive an email notification with further instructions.</p>
                                        <div class="mt-3 p-3 bg-white rounded border">
                                            <p class="font-medium">Track your application:</p>
                                            <p class="text-xs mt-1">Application ID: <span id="applicationId" class="font-mono font-bold"></span></p>
                                            <p class="text-xs">Status Token: <span id="statusToken" class="font-mono font-bold"></span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-center pt-4">
                            <button type="submit" id="submitButton" 
                                class="w-full bg-coffee-brown hover:bg-hover-brown text-white font-semibold py-3 px-6 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <span id="submitButtonText">Submit Application</span>
                                <div id="loadingSpinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin ml-2"></div>
                            </button>
                        </div>

                        <!-- Status Check Link -->
                        <div class="text-center pt-4 border-t">
                            <p class="text-sm text-gray-600">Already applied? 
                                <a href="{{ route('supplier.check-status') }}" class="text-coffee-brown hover:text-hover-brown font-semibold">Check your application status</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('supplierApplicationForm');
    const submitButton = document.getElementById('submitButton');
    const submitButtonText = document.getElementById('submitButtonText');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const errorMessages = document.getElementById('errorMessages');
    const errorList = document.getElementById('errorList');
    const successMessage = document.getElementById('successMessage');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Reset UI
        hideMessages();
        setLoadingState(true);
        
        // Create FormData
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("supplier.apply.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showSuccessMessage(data.data);
                form.reset();
            } else {
                showErrorMessages(data.errors || { general: [data.message] });
            }
        } catch (error) {
            console.error('Error:', error);
            showErrorMessages({ general: ['An unexpected error occurred. Please try again.'] });
        } finally {
            setLoadingState(false);
        }
    });

    function setLoadingState(loading) {
        submitButton.disabled = loading;
        submitButtonText.textContent = loading ? 'Submitting...' : 'Submit Application';
        loadingSpinner.classList.toggle('hidden', !loading);
    }

    function hideMessages() {
        errorMessages.classList.add('hidden');
        successMessage.classList.add('hidden');
    }

    function showErrorMessages(errors) {
        errorList.innerHTML = '';
        
        Object.values(errors).flat().forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });
        
        errorMessages.classList.remove('hidden');
        errorMessages.scrollIntoView({ behavior: 'smooth' });
    }

    function showSuccessMessage(data) {
        document.getElementById('applicationId').textContent = data.application_id;
        document.getElementById('statusToken').textContent = data.status_token;
        successMessage.classList.remove('hidden');
        successMessage.scrollIntoView({ behavior: 'smooth' });
    }
});
</script>
@endsection
