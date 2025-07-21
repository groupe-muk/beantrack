<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Supplier Portal - {{ config('app.name') }}</title>
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
                <div>
                    <a href="{{ route('onboarding') }}" class="text-light-brown hover:text-coffee-brown font-bold">← Back to Home</a>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <section class="relative bg-gradient-to-br from-coffee-brown to-hover-brown text-white min-h-[300px] flex items-center" 
                 style="background-image: linear-gradient(rgba(139, 69, 19, 0.7), rgba(101, 67, 33, 0.7)), url('{{ asset('/warehouse.image.webp') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
            <div class="relative max-w-6xl mx-auto px-6 text-center">
                <h1 class="text-4xl md:text-5xl font-bold mb-6 pt-7">Welcome to BeanTrack Supplier Portal</h1>
                <p class="text-xl md:text-2xl mb-8 opacity-90 max-w-3xl mx-auto">
                    Your gateway to joining our trusted network of coffee suppliers or accessing your supplier dashboard
                </p>
            </div>
        </section>

        <!-- Main Content -->
        <section class="py-10 px-6">
            <div class="max-w-6xl mx-auto">
                <!-- Existing Suppliers Section -->
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-coffee-brown mb-4">Already a BeanTrack Supplier?</h2>
                    <p class="text-lg text-gray-600 mb-8">Access your supplier dashboard to manage your coffee supplies and orders</p>
                    <div class="flex justify-center space-x-4">

                        <a href="{{ route('show.login', ['role' => 'supplier']) }}" class="bg-light-brown hover:bg-hover-brown text-white px-8 py-3 rounded-lg font-semibold transition-colors">
                            Login to Dashboard
                        </a>
                        <a href="{{ route('supplier.check-status') }}" class="bg-white border-2 border-light-brown text-light-brown hover:bg-brown hover:border-brown hover:text-white px-8 py-3 rounded-lg font-semibold transition-colors">

                            Check Application Status
                        </a>
                    </div>
                </div>

                <!-- New Suppliers Section -->
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-coffee-brown mb-4">New to BeanTrack?</h2>
                    <p class="text-lg text-gray-600 mb-8">Join our network of trusted coffee suppliers and start building your business with us</p>
                    
                    <!-- Benefits Grid -->
                    <div class="grid md:grid-cols-3 gap-8 mb-12">
                        <div class="bg-white p-6 rounded-lg shadow-md">

                            <div class="w-16 h-16 bg-light-brown rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-coffee-brown mb-2">Quality Focused</h3>
                            <p class="text-gray-600">We prioritize quality over quantity, ensuring your coffee meets our premium standards</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="w-16 h-16 bg-light-brown rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-coffee-brown mb-2">Fair Pricing</h3>
                            <p class="text-gray-600">Competitive rates and transparent pricing with timely payments</p>
                        </div>
                        
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <div class="w-16 h-16 bg-light-brown rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-coffee-brown mb-2">Growing Network</h3>
                            <p class="text-gray-600">Join our expanding network of coffee suppliers and grow your business</p>
                        </div>
                    </div>
                    <a href="{{ route('supplier.apply') }}" class="bg-light-brown hover:bg-hover-brown text-white px-10 py-4 rounded-lg font-semibold text-lg transition-colors">
                        Apply as Supplier
                    </a>
                </div>

                <!-- Requirements Section -->
                <div class="bg-gray-50 rounded-lg p-8 mb-16">
                    <h2 class="text-2xl font-bold text-coffee-brown mb-6 text-center">Supplier Requirements</h2>
                    <div class="grid md:grid-cols-2 gap-8">
                        <div>
                            <h3 class="text-lg font-semibold text-coffee-brown mb-4">Required Documents</h3>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Valid Trading License
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Bank Statement (3 months)
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-coffee-brown mb-4">Application Process</h3>
                            <ul class="space-y-2 text-gray-600">
                                <li class="flex items-center">
                                    <span class="w-6 h-6 bg-light-brown text-white rounded-full flex items-center justify-center text-sm mr-3">1</span>
                                    Submit application with required documents
                                </li>
                                <li class="flex items-center">
                                    <span class="w-6 h-6 bg-light-brown text-white rounded-full flex items-center justify-center text-sm mr-3">2</span>
                                    Document verification & financial review
                                </li>
                                <li class="flex items-center">
                                    <span class="w-6 h-6 bg-light-brown text-white rounded-full flex items-center justify-center text-sm mr-3">3</span>
                                    Site visit & quality assessment
                                </li>
                                <li class="flex items-center">
                                    <span class="w-6 h-6 bg-light-brown text-white rounded-full flex items-center justify-center text-sm mr-3">4</span>
                                    Approval & onboarding
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-coffee-brown mb-6">Frequently Asked Questions</h2>
                    <div class="space-y-4 max-w-3xl mx-auto">
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="font-semibold text-coffee-brown mb-2">How long does the application process take?</h3>
                            <p class="text-gray-600">Typically 5-7 business days from submission to approval, depending on document verification.</p>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="font-semibold text-coffee-brown mb-2">What are the minimum quality requirements?</h3>
                            <p class="text-gray-600">We require quality coffee that meets our standards. Our team will assess your product during the site visit.</p>
                        </div>
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="font-semibold text-coffee-brown mb-2">How do I track my application status?</h3>
                            <p class="text-gray-600">You'll receive a tracking token after submission. Use it to check your application status anytime.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-coffee-brown text-white py-8">
            <div class="max-w-6xl mx-auto px-6 text-center">
                <div class="flex justify-center items-center mb-4">
                    <img src="{{ asset('images/logo/beantrack-color-logo.png') }}" class="h-8 mr-3" alt="BeanTrack Logo" />
                    <span class="text-2xl font-bold">BeanTrack</span>
                </div>
                <p class="mb-4">Connecting coffee suppliers with quality-focused buyers</p>
                <div class="flex justify-center space-x-6">
                    <a href="#" class="hover:text-gray-300">Privacy Policy</a>
                    <a href="#" class="hover:text-gray-300">Terms of Service</a>
                    <a href="#" class="hover:text-gray-300">Contact Support</a>
                </div>
                <div class="mt-6 pt-6 border-t border-gray-600">
                    <p>&copy; 2024 BeanTrack. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </body>
</html>
