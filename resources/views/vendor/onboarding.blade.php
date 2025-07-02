<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Vendor Portal - {{ config('app.name') }}</title>
        <link rel="icon" href="{{ asset('images/logo/beantrack-color-logo.png') }}" type="image/png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="font-serif bg-white">
        <!-- Navigation Bar -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
                <a href="{{ route('onboarding') }}" class="flex items-center space-x-3 rtl:space-x-reverse">
                    <img src="{{ asset('images/logo/beantrack-color-logo.png') }}" class="h-8" alt="BeanTrack Logo" />
                    <span class="self-center text-2xl font-semibold whitespace-nowrap text-coffee-brown">BeanTrack</span>
                </a>
                <div class="text-sm">
                    <a href="{{ route('onboarding') }}" class="text-brown hover:text-coffee-brown">← Back to Home</a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-coffee-brown to-hover-brown text-white py-20">
            <div class="absolute inset-0 bg-black opacity-10"></div>
            <div class="relative max-w-6xl mx-auto px-6 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6">Welcome to BeanTrack Vendor Portal</h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                    Your gateway to joining our trusted network of coffee suppliers or accessing your vendor dashboard
                </p>
                <div class="flex justify-center">
                    <img src="{{ asset('images/warehouse.image.webp') }}" alt="Coffee Supply Chain" 
                         class="rounded-lg shadow-2xl max-w-md w-full opacity-90">
                </div>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-16 px-6">
            <div class="max-w-6xl mx-auto">
                <!-- Existing Vendors Section -->
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-coffee-brown mb-4">Already a BeanTrack Vendor?</h2>
                    <p class="text-brown text-lg mb-8 max-w-2xl mx-auto">
                        Access your vendor dashboard to manage inventory, view orders, and track deliveries.
                    </p>
                    
                    <div class="max-w-md mx-auto">
                        <a href="{{ route('show.login', ['role' => 'vendor']) }}" 
                           class="block bg-coffee-brown hover:bg-hover-brown text-white rounded-lg p-6 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <div class="flex items-center justify-center mb-3">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold mb-2">Login to Dashboard</h3>
                            <p class="text-sm opacity-90">Access your vendor account and manage your operations</p>
                        </a>
                    </div>
                </div>

                <!-- Divider -->
                <div class="flex items-center my-16">
                    <div class="flex-grow border-t border-soft-gray"></div>
                    <div class="flex-shrink-0 px-4">
                        <span class="text-brown font-medium">New to BeanTrack?</span>
                    </div>
                    <div class="flex-grow border-t border-soft-gray"></div>
                </div>

                <!-- New Vendor Application Section -->
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-coffee-brown mb-6">Become a BeanTrack Vendor</h2>
                    <p class="text-brown text-lg mb-12 max-w-3xl mx-auto">
                        Join our trusted network of coffee suppliers. Apply today to become a verified vendor 
                        and start supplying quality coffee beans to our network. Our streamlined application 
                        process includes automated verification for faster approval.
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                        <a href="{{ route('vendor.apply') }}" 
                           class="block bg-coffee-brown hover:bg-hover-brown text-white rounded-lg p-8 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <div class="flex items-center justify-center mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3">Submit Application</h3>
                            <p class="text-sm opacity-90 mb-4">Complete our vendor application with required documents</p>
                            <div class="text-left text-sm opacity-80">
                                <p class="mb-1">• Business information</p>
                                <p class="mb-1">• Bank statement (PDF)</p>
                                <p>• Trading license (PDF)</p>
                            </div>
                        </a>

                        <a href="{{ route('vendor.check-status') }}" 
                           class="block bg-white hover:bg-light-background border-2 border-coffee-brown text-coffee-brown rounded-lg p-8 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            <div class="flex items-center justify-center mb-4">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-semibold mb-3">Check Application Status</h3>
                            <p class="text-sm mb-4">Track your application progress using your status token</p>
                            <div class="text-left text-sm">
                                <p class="mb-1">• Real-time status updates</p>
                                <p class="mb-1">• Review timeline</p>
                                <p>• Next steps guidance</p>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Process Timeline -->
                <div class="bg-light-background rounded-lg p-8 mb-16">
                    <h3 class="text-2xl font-bold text-coffee-brown text-center mb-8">Application Process</h3>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div class="text-center">
                            <div class="w-12 h-12 bg-coffee-brown text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">1</div>
                            <h4 class="font-semibold text-coffee-brown mb-2">Submit Application</h4>
                            <p class="text-sm text-brown">Fill out the form and upload required documents</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-coffee-brown text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">2</div>
                            <h4 class="font-semibold text-coffee-brown mb-2">Automated Review</h4>
                            <p class="text-sm text-brown">Our system validates your documents and information</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-coffee-brown text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">3</div>
                            <h4 class="font-semibold text-coffee-brown mb-2">Admin Verification</h4>
                            <p class="text-sm text-brown">Final review and approval by our team</p>
                        </div>
                        <div class="text-center">
                            <div class="w-12 h-12 bg-coffee-brown text-white rounded-full flex items-center justify-center mx-auto mb-3 font-bold">4</div>
                            <h4 class="font-semibold text-coffee-brown mb-2">Account Creation</h4>
                            <p class="text-sm text-brown">Get access to your vendor dashboard</p>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-coffee-brown mb-8">Frequently Asked Questions</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto text-left">
                        <div class="bg-white border border-soft-gray rounded-lg p-6">
                            <h4 class="font-semibold text-coffee-brown mb-3">How long does the approval process take?</h4>
                            <p class="text-sm text-brown">Typically 1-3 business days. You'll receive email updates throughout the process.</p>
                        </div>
                        <div class="bg-white border border-soft-gray rounded-lg p-6">
                            <h4 class="font-semibold text-coffee-brown mb-3">What documents do I need?</h4>
                            <p class="text-sm text-brown">A recent bank statement and valid trading license, both in PDF format (max 10MB each).</p>
                        </div>
                        <div class="bg-white border border-soft-gray rounded-lg p-6">
                            <h4 class="font-semibold text-coffee-brown mb-3">Can I track my application status?</h4>
                            <p class="text-sm text-brown">Yes! Use the status token provided after submission to track your progress anytime.</p>
                        </div>
                        <div class="bg-white border border-soft-gray rounded-lg p-6">
                            <h4 class="font-semibold text-coffee-brown mb-3">What happens after approval?</h4>
                            <p class="text-sm text-brown">You'll receive login credentials and access to your vendor dashboard to start operations.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-coffee-brown text-white py-8">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <div class="flex items-center justify-center mb-4">
                    <img src="{{ asset('images/logo/beantrack-color-logo.png') }}" class="h-6 mr-2" alt="BeanTrack Logo" />
                    <span class="text-xl font-semibold">BeanTrack</span>
                </div>
                <p class="text-sm opacity-80">Connecting coffee suppliers with buyers worldwide</p>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    </body>
</html>
