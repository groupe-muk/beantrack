@extends('layouts.app')

@section('content')
<div class="flex min-h-screen bg-white">
    <!-- Left side - Image -->
    <div class="w-1/2 relative">
        <img src="{{ asset('/images/warehouse.image.webp') }}" alt="warehouse" class="w-full h-full object-cover sticky top-0 left-0">
        <div class="absolute inset-0 bg-coffee-brown bg-opacity-20"></div>
        <div class="absolute top-8 left-8">
            <div class="flex items-center">
                <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-8 h-8">
                <h1 class="text-white text-2xl font-semibold ml-2">BeanTrack</h1>
            </div>
        </div>
        <div class="absolute bottom-8 left-8 text-white">
            <h2 class="text-3xl font-bold mb-2">Join Our Supply Network</h2>
            <p class="text-lg opacity-90">Become a trusted vendor in our coffee supply chain</p>
        </div>
    </div>

    <!-- Right side - Form -->
    <div class="w-1/2 flex items-start justify-center overflow-y-auto">
        <div class="w-full max-w-md px-8 py-12">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-semibold text-light-brown mb-3">Vendor Application</h1>
                <p class="text-brown">Submit your application to become a BeanTrack vendor</p>
            </div>

            <!-- Application Form -->
            <form id="vendorApplicationForm" class="space-y-6">
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
                                   class="w-full border-soft-gray rounded border-2 h-12 px-3 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-light-gray file:text-coffee-brown hover:file:bg-soft-gray">
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
                                   class="w-full border-soft-gray rounded border-2 h-12 px-3 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-light-gray file:text-coffee-brown hover:file:bg-soft-gray">
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
                            <svg class="h-5 w-5 text-status-text-red" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-status-text-red">Please correct the following errors:</h3>
                            <div id="errorList" class="mt-2 text-sm text-status-text-red">
                                <ul class="list-disc list-inside space-y-1"></ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" id="submitBtn" 
                        class="w-full text-white rounded-lg p-3 font-semibold mt-8 bg-coffee-brown hover:bg-hover-brown focus:outline-none focus:ring-4 focus:ring-coffee-brown transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="submitText">Submit Application</span>
                    <span id="loadingText" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Submitting...
                    </span>
                </button>

                <!-- Additional Info -->
                <div class="text-center text-sm text-brown mt-6">
                    <p>By submitting this application, you agree to our terms and conditions.</p>
                    <p class="mt-2">All information will be kept confidential and used solely for application review.</p>
                </div>
            </form>

            <!-- Back to Home -->
            <div class="text-center mt-8 space-y-2">
                <div>
                    <a href="{{ route('vendor.check-status') }}" class="text-coffee-brown hover:text-hover-brown font-medium">
                        Check Application Status
                    </a>
                </div>
                <div>
                    <a href="{{ route('vendor.onboarding') }}" class="text-brown hover:text-coffee-brown">
                        ← Back to Vendor Portal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-status-background-green sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-status-text-green" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Application Submitted Successfully!
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Thank you for your application! We have received your submission and our team will review it shortly.
                            </p>
                            <div class="mt-4 p-4 bg-light-background rounded-lg">
                                <p class="text-sm font-semibold text-coffee-brown">What happens next?</p>
                                <ul class="mt-2 text-sm text-brown space-y-1">
                                    <li>• Your application will be reviewed within 1-3 business days</li>
                                    <li>• You'll receive an email confirmation with your application status</li>
                                    <li>• Our team may contact you for additional information if needed</li>
                                </ul>
                            </div>
                            <div class="mt-4 p-3 bg-status-background-blue rounded border border-blue-200">
                                <p class="text-sm text-status-text-blue">
                                    <strong>Application ID:</strong> <span id="applicationId" class="font-mono"></span><br>
                                    <strong>Status Token:</strong> <span id="statusToken" class="font-mono"></span>
                                </p>
                                <p class="text-xs text-status-text-blue mt-2">
                                    Save these details to check your application status later.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="closeModal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-coffee-brown text-base font-medium text-white hover:bg-hover-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-coffee-brown sm:ml-3 sm:w-auto sm:text-sm">
                    Continue
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('vendorApplicationForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingText = document.getElementById('loadingText');
    const errorMessages = document.getElementById('errorMessages');
    const errorList = document.querySelector('#errorList ul');
    const successModal = document.getElementById('successModal');
    const closeModalBtn = document.getElementById('closeModal');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Clear previous errors
        hideErrors();
        
        // Show loading state
        setLoading(true);
        
        try {
            const formData = new FormData(form);
            
            const response = await fetch('{{ route("vendor.apply.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value
                }
            });
            
            const data = await response.json();
            console.log(data);
            
            if (data.success) {
                // Show success modal with application details
                document.getElementById('applicationId').textContent = data.data.application_id;
                document.getElementById('statusToken').textContent = data.data.status_token;
                showSuccessModal();
            } else {
                // Show validation errors
                if (data.errors) {
                    showErrors(data.errors);
                } else {
                    showErrors({ general: [data.message || 'An error occurred while submitting your application.'] });
                }
            }
        } catch (error) {
            console.error('Submission error:', error);
            showErrors({ general: ['Network error. Please check your connection and try again.'] });
        } finally {
            setLoading(false);
        }
    });

    closeModalBtn.addEventListener('click', function() {
        // Redirect to vendor onboarding page
        window.location.href = '{{ route("vendor.onboarding") }}';
    });

    function setLoading(loading) {
        submitBtn.disabled = loading;
        submitText.classList.toggle('hidden', loading);
        loadingText.classList.toggle('hidden', !loading);
    }

    function showErrors(errors) {
        errorList.innerHTML = '';
        
        for (const [field, messages] of Object.entries(errors)) {
            messages.forEach(message => {
                const li = document.createElement('li');
                li.textContent = message;
                errorList.appendChild(li);
            });
        }
        
        errorMessages.classList.remove('hidden');
        errorMessages.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideErrors() {
        errorMessages.classList.add('hidden');
    }

    function showSuccessModal() {
        successModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    // File upload validation
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                // Check file size (10MB limit)
                if (file.size > 10 * 1024 * 1024) {
                    alert('File size must be less than 10MB');
                    this.value = '';
                    return;
                }
                
                // Check file type
                if (file.type !== 'application/pdf') {
                    alert('Only PDF files are allowed');
                    this.value = '';
                    return;
                }
                
                // Check filename requirements
                const fileName = file.name.toLowerCase();
                if (this.name === 'bank_statement' && !fileName.includes('bank-statement')) {
                    alert('Bank statement file must contain "bank-statement" in the filename (e.g., "company-bank-statement.pdf")');
                    this.value = '';
                    return;
                }
                
                if (this.name === 'trading_license' && !fileName.includes('trading-license')) {
                    alert('Trading license file must contain "trading-license" in the filename (e.g., "company-trading-license.pdf")');
                    this.value = '';
                    return;
                }
            }
        });
    });
});
</script>
@endsection
