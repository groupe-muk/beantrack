@extends('layouts.app')

@section('content')
    <!-- Header section -->
    <section class="text-center py-16 px-6 max-w-3xl mx-auto">
        <h1 class="text-3xl md:text-4xl font-bold mb-4">Track your coffee beans from farm to cup</h1>
        <p class="text-gray-600 text-base md:text-lg">Manage your coffee bean supply chain efficiently and transparently with BeanTrack.</p>
    </section>

    <!-- Features Section -->
    <section class="px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
        <!-- Admin Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/landing-page-image-1.jpg" alt="Admin" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Admin</h3>
                <p class="text-sm text-gray-600 mt-2">Manage users, roles, and system settings.</p>
            </div>
        </div>

        <!-- Supplier Card -->
        <div class=" shadow rounded-lg overflow-hidden text-center ">
            <img src="/images/landing-page-image-2.jpg" alt="Supplier" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Supplier</h3>
                <p class="text-sm text-gray-600 mt-2">Track bean shipments, update inventory, and manage orders.</p>
            </div>
        </div>

        <!-- Vendor Card -->
        <div class="bg-white shadow rounded-lg overflow-hidden text-center">
            <img src="/images/landing-page-image-3.jpg" alt="Vendor" class="w-full h-48 object-cover"> <!-- Replace path -->
            <div class="p-4">
                <h3 class="font-semibold text-lg">Vendor</h3>
                <p class="text-sm text-gray-600 mt-2">View inventory, place orders, and track deliveries.</p>
            </div>
        </div>
    </section>

@endsection    

