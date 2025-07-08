@extends('layouts.main-view')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-coffee-brown flex items-center gap-2">
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7m-9 4v4m4-4v4m-8-4v4M5 7h14l-1.447-2.894A1 1 0 0016.618 3H7.382a1 1 0 00-.895.553L5 7z" />
            </svg>
            Warehouse Management
        </h1>
        <p class="mt-1 text-coffee-brown">Manage your warehouses and logistics operations</p>
    </div>

    
    <div class="flex justify-end mb-4">
        <button id="addWarehouseBtn" onclick="toggleForm()" class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded flex items-center gap-1 mb-6">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Warehouse
        </button>
    </div>

    
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
        <div class="bg-amber-800 p-4 rounded-lg">
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


       
        <dialog id="edit-warehouse-{{ $supplycenter->id }}" class="p-6 rounded bg-white shadow-lg max-w-md">
            <form method="POST" action="{{ route('supplycenters.update', $supplycenter) }}">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold mb-4">Edit Warehouse</h3>
                <input name="name" value="{{ $supplycenter->name }}" class="border p-2 w-full mb-2" placeholder="Name">
                <input name="location" value="{{ $supplycenter->location }}" class="border p-2 w-full mb-2" placeholder="Location">
                <input name="manager" value="{{ $supplycenter->manager }}" class="border p-2 w-full mb-2" placeholder="Manager">
                <select name="status" class="border p-2 w-full mb-2">
                    <option value="active" {{ $supplycenter->status == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $supplycenter->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <input name="capacity" type="number" value="{{ $supplycenter->capacity }}" class="border p-2 w-full mb-4" placeholder="Capacity">
                <div class="flex justify-between">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
                    <button type="button" onclick="this.closest('dialog').close()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </dialog>
        @endforeach
    </div>
</div>

<script>
    function toggleForm() {
        const formDiv = document.getElementById('warehouseForm');
        formDiv.classList.toggle('hidden');
        document.body.style.overflow = formDiv.classList.contains('hidden') ? '' : 'hidden';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const formDiv = document.getElementById('warehouseForm');
            if (formDiv && !formDiv.classList.contains('hidden')) toggleForm();
        }
    });
</script>
@endsection
