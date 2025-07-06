@extends('layouts.app')

@section('content')

    <div class="absolute top-6 left-8 flex items-center border-b h-17 pr-3">
        <img src="{{ asset('/images/logo/beantrack-color-logo.png') }}" alt="BeanTrack Logo" class="w-7 h-7">
        <h1 class="text-coffee-brown text-3xl font-semibold ml-2">BeanTrack</h1>
    </div>


<div class="flex">
    
    <div class="w-64 bg-coffee-brown text-white p-4 mt-24">
        
        <ul>
            <li class="mb-2"><a href="#" class="hover:text-gray-300">Dashboard</a></li>
            <li class="mb-2"><a href="#" class="hover:text-gray-300">User Management</a></li>
            <li class="mb-2"><a href="#" class="hover:text-gray-300">Orders</a></li>
            <li class="mb-2"><a href="#" class="hover:text-gray-300">Inventory</a></li>
            <li class="mb-2"><a href="#" class="hover:text-gray-300">Workforce</a></li>
            <li class="mb-2"><a href="#" class="hover:text-gray-300">Reports</a></li>
        </ul>
        </div>
<div class="flex-1 p-6 flex flex-col items-center justify-center min-h-screen">
    <h1 class="text-2xl font-bold text-coffee-brown mb-4">Workforce Distribution</h1>
    <p class="text-coffee-brown mb-4">Manage and optimize the distribution of workforce across warehouses.</p>
    <input type="text" placeholder="Search" class="w-full p-2 mb-4 border rounded">

    
    <iframe
        width="100%"
        height="350"
        style="border:0; border-radius: 0.5rem;"
        loading="lazy"
        allowfullscreen
        referrerpolicy="no-referrer-when-downgrade"
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3153.019049858978!2d-122.41941518468144!3d37.77492977975937!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8085808c7e0e7e1d%3A0x8e8f7e8e8e8e8e8e!2sSan%20Francisco%2C%20CA!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus">
    </iframe>

    
    <div class="flex w-full max-w-4xl mt-8">
       
        <div class="flex-1 text-left">
            <h2 class="text-lg text-brown font-bold mb-2">Warehouse Details</h2>
            <a href="{{ route('show.SupplyCenter1') }}" class="block text-coffee-brown font-semibold hover:text-light-brown mb-8">Supply Center 1</a>
            <a href="{{ route('show.SupplyCenter2') }}" class="block text-coffee-brown font-semibold hover:text-light-brown mb-8">Supply Center 2</a>
            <a href="{{ route('show.SupplyCenter3') }}" class="block text-coffee-brown font-semibold hover:text-light-brown mb-4">Supply Center 3</a>
        </div>
       
        <div class="ml-8">
            <img src="{{ asset('/images/Landing-page-image-2.jpg') }}" alt="Warehouse Image" class="w-96 h-72 object-cover rounded-lg mb-4 px-8 py-8">
        </div>
    </div>
</div>
@endsection