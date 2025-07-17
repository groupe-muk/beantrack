@extends('layouts.main-view')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-coffee-brown flex items-center gap-2">
            Warehouse Management
        </h1>
        <p class="mt-1 text-coffee-brown">Manage your vendor warehouses and workforce distribution</p>
    </div>

    <div class="flex justify-end mb-4 gap-2">
        <button id="removeWorkersBtn" onclick="toggleRemoveWorkersModal()" class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Remove Workers
        </button>        
        <button id="uploadWorkersBtn" onclick="toggleUploadForm()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Upload Workers
        </button>
        <button id="addWarehouseBtn" onclick="toggleForm()" class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded flex items-center gap-1">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Warehouse
        </button>
    </div>

    <!-- Upload Workers Modal -->
    <div id="uploadWorkersForm" class="hidden fixed inset-0 bg-coffee-brown bg-opacity-40 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white p-6 rounded shadow-md w-full max-w-lg relative">
                <button onclick="toggleUploadForm()" class="absolute top-2 right-2 text-coffee-brown hover:text-light-brown text-4xl">&times;</button>
                <form action="{{ route('vendor.warehouses.upload.workers') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <h2 class="text-lg font-bold mb-4">Upload Workers Spreadsheet</h2>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Excel/CSV File</label>
                        <input type="file" name="worker_file" accept=".xlsx,.xls,.csv" required class="border p-2 w-full">
                        <p class="text-sm text-gray-600 mt-1">
                            Expected columns: Name, Role, Shift, Email (optional), Phone (optional), Address (optional)
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded" type="submit">Upload Workers</button>
                        <button type="button" onclick="toggleUploadForm()" class="bg-gray-300 text-gray-700 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div id="warehouseForm" class="hidden fixed inset-0 bg-coffee-brown bg-opacity-40 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-brown p-6 rounded shadow-md w-full max-w-lg relative" style="background-image: url('/images/Landing-page-image-3.jpg'); background-size: cover; background-position: center;">
                <button onclick="toggleForm()" class="absolute top-2 right-2 text-coffee-brown hover:text-light-brown text-4xl">&times;</button>
                <form action="{{ route('vendor.warehouses.store') }}" method="POST">
                    @csrf
                    <h2 class="text-lg font-bold mb-2">Add New Warehouse</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <input name="name" placeholder="Name" required class="border p-2 placeholder:text-black">
                        <input name="location" placeholder="Location" required class="border p-2 placeholder:text-black">
                        <input name="manager_name" placeholder="Manager" required class="border p-2 placeholder:text-black">
                        <input name="capacity" placeholder="Capacity" required type="number" class="border p-2">
                    </div>
                    <div class="flex gap-2 mt-3">
                        <button class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded" type="submit">Add Warehouse</button>
                        <button type="button" onclick="toggleForm()" class="bg-brown text-white hover:bg-light-brown px-4 py-2 rounded">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Remove Workers Modal -->
    <div id="removeWorkersForm" class="hidden fixed inset-0 bg-coffee-brown bg-opacity-40 z-50" style="display: none;">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white p-6 rounded shadow-md w-full max-w-lg relative max-h-96 overflow-y-auto">
                <button onclick="toggleRemoveWorkersModal()" class="absolute top-2 right-2 text-coffee-brown hover:text-light-brown text-4xl">&times;</button>
                <form action="{{ route('vendor.warehouses.workers.bulk.delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <h2 class="text-lg font-bold mb-4">Remove Workers</h2>
                    <p class="text-sm text-gray-600 mb-4">Select workers to remove:</p>
                    <div class="max-h-64 overflow-y-auto mb-4">
                        @foreach ($warehouses as $warehouse)
                        @foreach ($warehouse->workers as $worker)
                        <div class="flex items-center p-2 border-b">
                            <input type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" class="mr-3" id="worker-{{ $worker->id }}">
                            <label for="worker-{{ $worker->id }}" class="flex-1 cursor-pointer">
                                <div class="font-medium">{{ $worker->name }}</div>
                                <div class="text-sm text-gray-600">{{ $worker->role }} - {{ $warehouse->name }}</div>
                            </label>
                        </div>
                        @endforeach
                        @endforeach
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded" onclick="return confirm('Are you sure you want to remove selected workers?')">Remove Selected</button>
                        <button type="button" onclick="toggleRemoveWorkersModal()" class="bg-gray-300 text-gray-700 hover:bg-gray-400 px-4 py-2 rounded">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <x-stats-card
            title="Total Warehouses"
            :value="$warehouses->count()"
            iconClass="fa-warehouse"
            iconColorClass="text-light-brown"
            id="total-warehouses-card"
        />
        <x-stats-card
            title="Total Staff"
            :value="$warehouses->sum(fn($w) => $w->workers->count())"
            iconClass="fa-users"
            iconColorClass="text-light-brown"
            id="total-staff-card"
        />
        <x-stats-card
            title="Total Capacity"
            :value="number_format($warehouses->sum('capacity')) . ' units'"
            iconClass="fa-weight-hanging"
            iconColorClass="text-light-brown"
            id="total-capacity-card"
        />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($warehouses as $warehouse)
        <div class="bg-white p-4 rounded-lg shadow transform transition-all duration-300 hover:scale-101 hover:shadow-xl cursor-pointer border border-gray-200 hover:border-coffee-brown relative group" 
             onclick="viewWarehouseDetails('{{ $warehouse->id }}')">
            <!-- Hover overlay -->
            <div class="absolute inset-0 bg-brown bg-opacity-10 rounded-lg opacity-0 group-hover:opacity-90 transition-opacity duration-300 flex items-center justify-center">
                <span class="text-brown font-semibold text-sm bg-white px-3 py-1 rounded shadow">Click to view details</span>
            </div>
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-lg font-bold text-coffee-brown">{{ $warehouse->name }}</h3>
                    <p class="text-gray-600">{{ $warehouse->location }}</p>
                    <p class="text-gray-600">Manager: {{ $warehouse->manager_name }}</p>
                    <p class="text-gray-600">Capacity: {{ number_format($warehouse->capacity) }} units</p>
                </div>
                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <button type="button" onclick="toggleEditModal('edit-warehouse-{{ $warehouse->id }}')" class="text-coffee-brown hover:underline text-sm flex items-center gap-1 transition-colors duration-200 hover:text-light-brown">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z" />
                        </svg>
                        Edit
                    </button>
                    <form method="POST" action="{{ route('vendor.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Delete this warehouse?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline text-sm flex items-center gap-1 transition-colors duration-200 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Delete
                        </button>
                    </form>
                </div>
            </div>

            <!-- Workers Section -->
            <div class="mt-4">
                <h4 class="font-bold text-sm mb-2 text-coffee-brown">Staff ({{ $warehouse->workers->count() }})</h4>
                @if($warehouse->workers->count() > 0)
                    @php
                        $shiftCounts = $warehouse->workers->groupBy('shift')->map->count();
                    @endphp
                    <div class="text-xs text-gray-600 mb-2">
                        @foreach(['Morning', 'Afternoon', 'Night'] as $shift)
                            @if(isset($shiftCounts[$shift]))
                                <span class="inline-block bg-light-brown text-white px-2 py-1 rounded mr-1 mb-1">
                                    {{ $shift }}: {{ $shiftCounts[$shift] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                    @foreach ($warehouse->workers->take(3) as $staff)
                    <div class="text-xs text-gray-600 mb-1 flex justify-between">
                        <span>{{ $staff->name }} ({{ $staff->role }})</span>
                        <span>{{ $staff->shift }}</span>
                    </div>
                    @endforeach
                    @if($warehouse->workers->count() > 3)
                        <p class="text-xs text-gray-500 italic">and {{ $warehouse->workers->count() - 3 }} more...</p>
                    @endif
                @else
                    <p class="text-xs text-gray-500">No staff assigned</p>
                @endif
            </div>
        </div>

        <!-- Edit warehouse modal -->
        <div id="edit-warehouse-{{ $warehouse->id }}" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
            <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
                <form method="POST" action="{{ route('vendor.warehouses.update', $warehouse) }}">
                    @csrf
                    @method('PATCH')
                    <h3 class="text-lg font-bold mb-4">Edit Warehouse</h3>
                    <div class="grid gap-3">
                        <input name="name" value="{{ $warehouse->name }}" class="border p-2 w-full rounded" placeholder="Name" required>
                        <input name="location" value="{{ $warehouse->location }}" class="border p-2 w-full rounded" placeholder="Location" required>
                        <input name="manager_name" value="{{ $warehouse->manager_name }}" class="border p-2 w-full rounded" placeholder="Manager" required>
                        <input name="capacity" value="{{ $warehouse->capacity }}" class="border p-2 w-full rounded" placeholder="Capacity" type="number" required>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded">Update</button>
                        <button type="button" onclick="toggleEditModal('edit-warehouse-{{ $warehouse->id }}')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script>
    function toggleForm() {
        const formDiv = document.getElementById('warehouseForm');
        if (formDiv.style.display === 'none' || formDiv.style.display === '') {
            formDiv.style.display = 'block';
            formDiv.classList.remove('hidden');
        } else {
            formDiv.style.display = 'none';
            formDiv.classList.add('hidden');
        }
        document.body.style.overflow = formDiv.style.display === 'none' ? '' : 'hidden';
    }

    function toggleUploadForm() {
        const formDiv = document.getElementById('uploadWorkersForm');
        if (formDiv.style.display === 'none' || formDiv.style.display === '') {
            formDiv.style.display = 'block';
            formDiv.classList.remove('hidden');
        } else {
            formDiv.style.display = 'none';
            formDiv.classList.add('hidden');
        }
        document.body.style.overflow = formDiv.style.display === 'none' ? '' : 'hidden';
    }

    function toggleRemoveWorkersModal() {
        const formDiv = document.getElementById('removeWorkersForm');
        if (formDiv.style.display === 'none' || formDiv.style.display === '') {
            formDiv.style.display = 'block';
            formDiv.classList.remove('hidden');
        } else {
            formDiv.style.display = 'none';
            formDiv.classList.add('hidden');
        }
        document.body.style.overflow = formDiv.style.display === 'none' ? '' : 'hidden';
    }

    function toggleEditModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
    }

    function viewWarehouseDetails(warehouseId) {
        window.location.href = `/vendor/warehouses/${warehouseId}`;
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const warehouseForm = document.getElementById('warehouseForm');
            const uploadForm = document.getElementById('uploadWorkersForm');
            const removeForm = document.getElementById('removeWorkersForm');
            
            if (warehouseForm && !warehouseForm.classList.contains('hidden')) {
                toggleForm();
            }
            if (uploadForm && !uploadForm.classList.contains('hidden')) {
                toggleUploadForm();
            }
            if (removeForm && !removeForm.classList.contains('hidden')) {
                toggleRemoveWorkersModal();
            }
        }
    });

    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.totalWarehouses !== undefined) {
                // Update stats cards with new data
                document.querySelector('#total-warehouses-card .text-2xl').textContent = data.totalWarehouses;
                document.querySelector('#total-staff-card .text-2xl').textContent = data.totalStaff;
                document.querySelector('#total-capacity-card .text-2xl').textContent = data.totalCapacity + ' units';
            }
        })
        .catch(error => console.log('Stats refresh failed:', error));
    }, 30000);
</script>
@endsection
