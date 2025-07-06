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

    

    <div id="warehouseForm" class="hidden fixed inset-0 bg-coffee-brown bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-brown p-6 rounded shadow-md w-full max-w-lg relative" style="background-image: url('/images/Landing-page-image-3.jpg'); background-size: cover; background-position: center;">
            <button onclick="toggleForm()" class="absolute top-2 right-2 text-coffee-brown hover:text-light-brown text-4xl">&times;</button>
            <form action="{{ route('supplycenters.store') }}" method="POST">
                @csrf
                <h2 class="text-lg font-bold mb-2">Add New Warehouse</h2>
                <div class="grid grid-cols-2 gap-4">
                    <input name="name" placeholder="Name" required class="border p-2 placeholder:text-black">
                    <input name="location" placeholder="Location" required class="border p-2 placeholder:text-black">
                    <input name="manager" placeholder="Manager" required class="border p-2 placeholder:text-black">
                    <select name="status" required class="border p-2">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <input name="capacity" placeholder="Capacity" required type="number" class="border p-2">
                </div>
                <div class="flex gap-2 mt-3">
                    <button class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded" type="submit">Add Warehouse</button>
                    <button type="button" onclick="toggleForm()" class="bg-brown text-white hover:bg-light-brown px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    
    <div class="grid grid-cols-3 gap-4 mb-6 text-white">
        <div class="bg-light-brown p-4 rounded-lg">
            <h2 class="text-xl font-bold">Total Warehouses</h2>
            <p>{{ $supplycenters->count() }}</p>
        </div>
        <div class="bg-coffee-brown p-4 rounded-lg">
            <h2 class="text-xl font-bold">Active Warehouses</h2>
            <p>{{ $supplycenters->where('status', 'active')->count() }}</p>
        </div>
        <div class="bg-light-brown p-4 rounded-lg">
            <h2 class="text-xl font-bold">Total Staff</h2>
            <p>{{ $supplycenters->sum(fn($w) => $w->workers->count()) }}</p>
        </div>
    </div>

   
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($supplycenters as $supplycenter)
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-bold">{{ $supplycenter->name }}</h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('edit-warehouse-{{ $supplycenter->id }}').showModal()" class="text-coffee-brown hover:underline text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z" />
                        </svg>
                        Edit
                    </button>
                    <form method="POST" action="{{ route('supplycenters.destroy', $supplycenter) }}" onsubmit="return confirm('Delete this warehouse?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-brown-600 hover:underline text-sm flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-sm text-brown-600">
                <svg class="inline w-4 h-4 text-brown-500 mr-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10c0 7.5-7.5 11-7.5 11S4.5 17.5 4.5 10a7.5 7.5 0 1115 0z"/>
                </svg>
                {{ $supplycenter->location }}
            </p>
            <p class="text-sm text-brown-600">
                <svg class="inline w-4 h-4 text-brown-500 mr-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0v.75H4.5v-.75z"/>
                </svg>
                Manager: {{ $supplycenter->manager }}
            </p>

            @php
                $capacity = $supplycenter->capacity ?? 0;
                $current = $supplycenter->current_storage_lbs ?? 0;
                $utilization = ($capacity > 0) ? round(($current / $capacity) * 100) : 0;
                $utilizationBar = ($capacity > 0) ? ($current / $capacity) * 100 : 0;
            @endphp

            
            <div class="mb-3 mt-3">
                <div class="flex justify-between items-center text-sm text-brown-600 font-medium mb-1">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25V6.75A2.25 2.25 0 015.25 4.5h13.5A2.25 2.25 0 0121 6.75v1.5M3 8.25l9 6.75 9-6.75M3 8.25v9a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 17.25v-9" />
                        </svg>
                        Storage Utilization
                    </span>
                    <span>{{ $utilization }}%</span>
                </div>
                <div class="w-full h-2 bg-brown-100 rounded">
                    <div class="h-2 bg-blue-600 rounded" style="width: {{ $utilizationBar }}%"></div>
                </div>
                <div class="text-xs text-brown-400 mt-1">{{ number_format($current) }} / {{ number_format($capacity) }} lbs</div>
            </div>

            <h4 class="font-bold text-sm mb-1">Staff ({{ $supplycenter->workers->count() }})</h4>
            @foreach ($supplycenter->workers as $staff)
            <div class="flex justify-between items-start gap-2 border-b py-2">
                <div>
                    <p class="font-medium">{{ $staff->name }}</p>
                    <p class="text-xs text-light-brown">
                        <svg class="inline w-4 h-4 text-brown mr-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 18.75v-1.5a4.5 4.5 0 014.5-4.5h6a4.5 4.5 0 014.5 4.5v1.5M12 11.25a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                        {{ $staff->role }} • {{ $staff->shift }}
                    </p>
                </div>
                <button onclick="document.getElementById('edit-staff-{{ $staff->id }}').showModal()" class="text-sm text-brown-500 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z"/></svg> Edit
                </button>
            </div>
            @endforeach

            <form action="{{ route('worker.store', $supplycenter) }}" method="POST" class="mt-3">
                @csrf
                <input name="name" placeholder="Name" required class="border p-1 text-sm w-full mb-2">
                <input name="role" placeholder="Role" required class="border p-1 text-sm w-full mb-2">
                <input name="shift" placeholder="Shift" required class="border p-1 text-sm w-full mb-2">
                <button class="bg-coffee-brown hover:bg-light-brown text-white px-2 py-1 rounded text-sm flex items-center gap-1" type="submit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add
                </button>
            </form>
        </div>


       
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