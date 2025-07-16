<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>
        <link rel="icon" href="{{ asset('images/logo/beantrack-color-logo.png') }}" type="image/png">

       <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Work+Sans:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
       <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class = "font-serif" style = "background-color: white">
    <!-- Navigation Bar -->
    

<nav class="bg-white border-b border-gray-200 dark:bg-gray-900">
  <div class="max-w-screen-xl flex flex-wrap items-center justify-between mx-auto p-4">
    <a href="#" class="flex items-center space-x-3 rtl:space-x-reverse">
        <img src="/images/logo/beantrack-color-logo.PNG" class="h-8" alt="BeanTrack Logo" />
        <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">BeanTrack</span>
    </a>
  </div>
</nav>

    <!-- Header section -->
    <section class="text-center py-16 px-6 max-w-3xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Track your coffee beans from farm to cup</h1>
        <p class="text-dark-background text-base md:text-lg">Manage your coffee bean supply chain efficiently and transparently with BeanTrack.</p>
    </section>

    <!-- Features Section -->
    <section class="px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Admin Card -->
        <form action="{{ route('role.select') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="admin">
            <button type="submit" class="w-full">
                <div class="cursor-pointer transition hover:scale-110 bg-white shadow rounded-lg overflow-hidden text-center">
                    <img src="/images/landing-page-image-1.jpg" alt="Admin" class="w-full h-48 object-cover">
                    <div class="p-4">
                        <h3 class="font-semibold text-lg">Admin</h3>
                        <p class="text-sm text-gray-600 mt-2">Manage users, roles, and system settings.</p>
                    </div>
                </div>
            </button>
        </form>

        <!-- Supplier Card -->
        <a href="{{ route('supplier.onboarding') }}" class="w-full">
            <div class="cursor-pointer transition hover:scale-110 bg-white shadow rounded-lg overflow-hidden text-center">
                <img src="/images/landing-page-image-2.jpg" alt="Supplier" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">Supplier</h3>
                    <p class="text-sm text-gray-600 mt-2">Join our network of trusted coffee suppliers.</p>
                </div>
            </div>
        </a>

        <!-- Vendor Card -->
        <a href="{{ route('vendor.onboarding') }}" class="w-full">
            <div class="cursor-pointer transition hover:scale-110 bg-white shadow rounded-lg overflow-hidden text-center">
                <img src="/images/landing-page-image-3.jpg" alt="Vendor" class="w-full h-48 object-cover">
                <div class="p-4">
                    <h3 class="font-semibold text-lg">Vendor</h3>
                    <p class="text-sm text-gray-600 mt-2">View inventory, place orders, and track deliveries.</p>
                </div>
            </div>
        </a>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

 </body>
 </html>

