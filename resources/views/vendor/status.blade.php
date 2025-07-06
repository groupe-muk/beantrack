@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-light-background flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-8 h-8">
                <h1 class="text-coffee-brown text-2xl font-semibold ml-2">BeanTrack</h1>
            </div>
            <h2 class="text-3xl font-semibold text-light-brown mb-2">Check Application Status</h2>
            <p class="text-brown">Enter your status token to view your application progress</p>
        </div>

        <!-- Status Check Form -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <form id="statusCheckForm">
                @csrf
                <div class="mb-4">
                    <label for="token" class="block mb-2 text-coffee-brown font-semibold">Status Token</label>
                    <input type="text" id="token" name="token" required 
                           placeholder="Enter your 32-character status token"
                           class="w-full border-soft-gray rounded border-2 h-12 px-3 focus:border-light-brown focus:outline-none transition-colors font-mono text-sm">
                    <p class="text-sm text-brown mt-1">This token was provided when you submitted your application</p>
                </div>

                <button type="submit" id="checkBtn" 
                        class="w-full text-white rounded-lg p-3 font-semibold bg-coffee-brown hover:bg-hover-brown focus:outline-none focus:ring-4 focus:ring-coffee-brown transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="checkText">Check Status</span>
                    <span id="loadingText" class="hidden">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Checking...
                    </span>
                </button>
            </form>
        </div>

        <!-- Error Messages -->
        <div id="errorMessage" class="hidden bg-status-background-red border border-red-300 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-status-text-red" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-status-text-red" id="errorText"></p>
                </div>
            </div>
        </div>

        <!-- Status Result -->
        <div id="statusResult" class="hidden bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center mb-4">
                <div class="flex-shrink-0">
                    <div id="statusIcon" class="h-12 w-12 rounded-full flex items-center justify-center">
                        <!-- Icon will be inserted here -->
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-semibold text-gray-900" id="applicantName"></h3>
                    <p class="text-sm text-brown" id="businessName"></p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-coffee-brown">Status:</span>
                    <span id="statusBadge" class="px-3 py-1 rounded-full text-sm font-medium">
                        <!-- Status badge will be inserted here -->
                    </span>
                </div>

                <div id="validationMessage" class="hidden">
                    <span class="text-sm font-medium text-coffee-brown">Message:</span>
                    <p class="text-sm text-brown mt-1" id="messageText"></p>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-coffee-brown">Submitted:</span>
                        <p class="text-brown" id="submittedDate"></p>
                    </div>
                    <div id="validatedDateContainer" class="hidden">
                        <span class="font-medium text-coffee-brown">Reviewed:</span>
                        <p class="text-brown" id="validatedDate"></p>
                    </div>
                </div>

                <div id="visitScheduled" class="hidden bg-status-background-blue rounded-lg p-3">
                    <div class="flex">
                        <svg class="h-5 w-5 text-status-text-blue mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <div class="ml-2">
                            <p class="text-sm font-medium text-status-text-blue">Site Visit Scheduled</p>
                            <p class="text-sm text-status-text-blue" id="visitDate"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="text-center space-y-4">
            <a href="{{ route('vendor.apply') }}" class="inline-flex items-center text-coffee-brown hover:text-hover-brown font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Submit New Application
            </a>
            <div class="text-sm text-brown">
                <a href="{{ route('vendor.onboarding') }}" class="hover:text-coffee-brown">← Back to Vendor Portal</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('statusCheckForm');
    const checkBtn = document.getElementById('checkBtn');
    const checkText = document.getElementById('checkText');
    const loadingText = document.getElementById('loadingText');
    const errorMessage = document.getElementById('errorMessage');
    const errorText = document.getElementById('errorText');
    const statusResult = document.getElementById('statusResult');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const token = document.getElementById('token').value.trim();
        
        if (!token) {
            showError('Please enter your status token');
            return;
        }

        if (token.length !== 32) {
            showError('Status token must be exactly 32 characters long');
            return;
        }
        
        hideError();
        hideResult();
        setLoading(true);
        
        try {
            const response = await fetch('{{ route("vendor.application.status") }}?' + new URLSearchParams({
                token: token
            }), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showResult(data.data);
            } else {
                showError(data.message || 'Application not found with the provided token');
            }
        } catch (error) {
            console.error('Status check error:', error);
            showError('Network error. Please check your connection and try again.');
        } finally {
            setLoading(false);
        }
    });

    function setLoading(loading) {
        checkBtn.disabled = loading;
        checkText.classList.toggle('hidden', loading);
        loadingText.classList.toggle('hidden', !loading);
    }

    function showError(message) {
        errorText.textContent = message;
        errorMessage.classList.remove('hidden');
        errorMessage.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideError() {
        errorMessage.classList.add('hidden');
    }

    function showResult(data) {
        // Set applicant info
        document.getElementById('applicantName').textContent = data.applicant_name;
        document.getElementById('businessName').textContent = data.business_name;
        
        // Set status badge and icon
        const status = data.status;
        const statusBadge = document.getElementById('statusBadge');
        const statusIcon = document.getElementById('statusIcon');
        
        // Clear previous classes and content
        statusBadge.className = 'px-3 py-1 rounded-full text-sm font-medium';
        statusIcon.innerHTML = '';
        statusIcon.className = 'h-12 w-12 rounded-full flex items-center justify-center';
        
        switch (status) {
            case 'pending':
                statusBadge.classList.add('bg-status-background-orange', 'text-status-text-orange');
                statusBadge.textContent = 'Pending Review';
                statusIcon.classList.add('bg-status-background-orange');
                statusIcon.innerHTML = '<svg class="h-6 w-6 text-status-text-orange" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
                break;
            case 'under_review':
                statusBadge.classList.add('bg-status-background-blue', 'text-status-text-blue');
                statusBadge.textContent = 'Under Review';
                statusIcon.classList.add('bg-status-background-blue');
                statusIcon.innerHTML = '<svg class="h-6 w-6 text-status-text-blue" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
                break;
            case 'approved':
                statusBadge.classList.add('bg-status-background-green', 'text-status-text-green');
                statusBadge.textContent = 'Approved';
                statusIcon.classList.add('bg-status-background-green');
                statusIcon.innerHTML = '<svg class="h-6 w-6 text-status-text-green" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
                break;
            case 'rejected':
                statusBadge.classList.add('bg-status-background-red', 'text-status-text-red');
                statusBadge.textContent = 'Rejected';
                statusIcon.classList.add('bg-status-background-red');
                statusIcon.innerHTML = '<svg class="h-6 w-6 text-status-text-red" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
                break;
            default:
                statusBadge.classList.add('bg-status-background-gray', 'text-status-text-gray');
                statusBadge.textContent = 'Unknown';
                statusIcon.classList.add('bg-status-background-gray');
                statusIcon.innerHTML = '<svg class="h-6 w-6 text-status-text-gray" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
        }
        
        // Set validation message
        if (data.validation_message) {
            document.getElementById('messageText').textContent = data.validation_message;
            document.getElementById('validationMessage').classList.remove('hidden');
        } else {
            document.getElementById('validationMessage').classList.add('hidden');
        }
        
        // Set dates
        document.getElementById('submittedDate').textContent = formatDate(data.submitted_at);
        
        if (data.validated_at) {
            document.getElementById('validatedDate').textContent = formatDate(data.validated_at);
            document.getElementById('validatedDateContainer').classList.remove('hidden');
        } else {
            document.getElementById('validatedDateContainer').classList.add('hidden');
        }
        
        // Set visit scheduled
        if (data.visit_scheduled) {
            document.getElementById('visitDate').textContent = formatDate(data.visit_scheduled);
            document.getElementById('visitScheduled').classList.remove('hidden');
        } else {
            document.getElementById('visitScheduled').classList.add('hidden');
        }
        
        statusResult.classList.remove('hidden');
        statusResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function hideResult() {
        statusResult.classList.add('hidden');
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
});
</script>
@endsection
