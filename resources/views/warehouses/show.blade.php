@extends('layouts.main-view')

@section('content')
<div class="bg-coffee-light min-h-screen p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-coffee-brown">{{ $warehouse->name }}</h1>
                <p class="text-gray-600 pt-4">Warehouse Details</p>
            </div>    

            <div class="flex gap-2">
                @if($user->role === 'supplier')
                    <a href="{{ route('supplier.warehouses.index') }}" class="text-white transition-colors duration-200 flex items-center gap-4 bg-light-brown rounded px-4 py-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <p class="text-white">Back to Warehouses</p>
                    </a>
                @else
                    <a href="{{ route('vendor.warehouses.index') }}" class="text-white transition-colors duration-200 flex items-center gap-4 bg-light-brown rounded px-4 py-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <p class="text-white">Back to Warehouses</p>
                    </a>
                @endif
                
                <button onclick="toggleEditModal('edit-warehouse')" class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-dashboard-light transition-colors duration-200">
                    Edit Warehouse
                </button>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Warehouse Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Location</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ $warehouse->location }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Manager</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ $warehouse->manager_name }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Capacity</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ number_format($warehouse->capacity) }} kgs</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Total Workers</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ $warehouse->workers->count() }}</p>
            </div>
        </div>

        <!-- Workers Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-coffee-brown">Workers</h2>
                <div class="flex gap-2">
                    @if($warehouse->workers->count() > 0)
                        <button onclick="toggleMoveWorkersModal()" class="bg-light-brown text-white px-4 py-2 rounded hover:bg-brown transition-colors duration-200">
                            Move Workers
                        </button>
                    @endif
                    <button onclick="toggleAddWorkerModal()" class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-dashboard-light transition-colors duration-200">
                        Add Worker
                    </button>
                </div>
            </div>

            @if($warehouse->workers->count() > 0)
                <!-- Workers by Shift -->
                @php
                    $workersByShift = $warehouse->workers->groupBy('shift');
                @endphp
                
                @foreach(['Morning', 'Afternoon', 'Night'] as $shift)
                    @if(isset($workersByShift[$shift]) && $workersByShift[$shift]->count() > 0)
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-coffee-brown mb-3 border-b border-gray-200 pb-2">
                                {{ $shift }} Shift ({{ $workersByShift[$shift]->count() }} workers)
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($workersByShift[$shift] as $worker)
                                    <div class="bg-gray-50 p-4 rounded-lg border hover:shadow-md transition-shadow duration-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <h4 class="font-semibold text-coffee-brown">{{ $worker->name }}</h4>
                                                <p class="text-sm text-gray-600">{{ $worker->role }}</p>
                                                @if($worker->email)
                                                    <p class="text-xs text-gray-500">{{ $worker->email }}</p>
                                                @endif
                                                @if($worker->phone)
                                                    <p class="text-xs text-gray-500">{{ $worker->phone }}</p>
                                                @endif
                                            </div>
                                            <button onclick="toggleEditWorkerModal('{{ $worker->id }}')" class="text-coffee-brown hover:text-light-brown transition-colors duration-200">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center py-8 text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <p class="text-lg font-medium">No Workers Assigned</p>
                    <p class="text-sm">This warehouse doesn't have any workers yet. Add some to get started.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Warehouse Modal -->
    <div id="edit-warehouse" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            @if($user->role === 'supplier')
                <form method="POST" action="{{ route('supplier.warehouses.update', $warehouse) }}">
            @else
                <form method="POST" action="{{ route('vendor.warehouses.update', $warehouse) }}">
            @endif
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
                    <button type="button" onclick="toggleEditModal('edit-warehouse')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Worker Modal -->
    <div id="add-worker" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            @if($user->role === 'supplier')
                <form method="POST" action="{{ route('supplier.warehouses.workers.store', $warehouse) }}">
            @else
                <form method="POST" action="{{ route('vendor.warehouses.workers.store', $warehouse) }}">
            @endif
                @csrf
                <h3 class="text-lg font-bold mb-4">Add New Worker</h3>
                <div class="grid gap-3">
                    <input name="name" class="border p-2 w-full rounded" placeholder="Full Name" required>
                    <input name="role" class="border p-2 w-full rounded" placeholder="Job Role" required>
                    <select name="shift" class="border p-2 w-full rounded" required>
                        <option value="">Select Shift</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Night">Night</option>
                    </select>
                    <input name="email" type="email" class="border p-2 w-full rounded" placeholder="Email (optional)">
                    <input name="phone" class="border p-2 w-full rounded" placeholder="Phone (optional)">
                    <textarea name="address" class="border p-2 w-full rounded" placeholder="Address (optional)" rows="2"></textarea>
                </div>
                <div class="flex gap-2 mt-4">
                    <button type="submit" class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded">Add Worker</button>
                    <button type="button" onclick="toggleAddWorkerModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Workers Modal -->
    @if($warehouse->workers->count() > 0)
    <div id="move-workers" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-4xl w-full shadow-xl max-h-[80vh] overflow-y-auto">
            @if($user->role === 'supplier')
                <form method="POST" action="{{ route('supplier.warehouses.move.workers', $warehouse) }}">
            @else
                <form method="POST" action="{{ route('vendor.warehouses.move.workers', $warehouse) }}">
            @endif
                @csrf
                <h3 class="text-lg font-bold mb-4">Move Workers to Another Warehouse</h3>
                
                <div class="mb-4">
                    <label class="block mb-2 font-medium">Destination Warehouse</label>
                    <select name="destination_warehouse_id" class="border p-2 w-full rounded" required>
                        <option value="">Select destination warehouse</option>
                        @foreach($otherWarehouses as $otherWarehouse)
                            <option value="{{ $otherWarehouse->id }}">{{ $otherWarehouse->name }} - {{ $otherWarehouse->location }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-medium">Select Workers to Move</label>
                    <div class="max-h-64 overflow-y-auto border rounded p-4">
                        @foreach($warehouse->workers as $worker)
                            <label class="flex items-center gap-3 p-2 hover:bg-gray-50 cursor-pointer">
                                <input type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" class="rounded">
                                <div class="flex-1">
                                    <div class="font-medium">{{ $worker->name }}</div>
                                    <div class="text-sm text-gray-600">{{ $worker->role }} - {{ $worker->shift }} Shift</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="bg-light-brown hover:bg-brown text-white px-4 py-2 rounded">Move Selected Workers</button>
                    <button type="button" onclick="toggleMoveWorkersModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <!-- Edit Worker Modals -->
    @foreach($warehouse->workers as $worker)
    <div id="edit-worker-{{ $worker->id }}" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            @if($user->role === 'supplier')
                <form method="POST" action="{{ route('supplier.warehouses.workers.update', $worker) }}">
            @else
                <form method="POST" action="{{ route('vendor.warehouses.workers.update', $worker) }}">
            @endif
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold mb-4">Edit Worker</h3>
                <div class="grid gap-3">
                    <input name="name" value="{{ $worker->name }}" class="border p-2 w-full rounded" placeholder="Full Name" required>
                    <input name="role" value="{{ $worker->role }}" class="border p-2 w-full rounded" placeholder="Job Role" required>
                    <select name="shift" class="border p-2 w-full rounded" required>
                        <option value="Morning" {{ $worker->shift == 'Morning' ? 'selected' : '' }}>Morning</option>
                        <option value="Afternoon" {{ $worker->shift == 'Afternoon' ? 'selected' : '' }}>Afternoon</option>
                        <option value="Night" {{ $worker->shift == 'Night' ? 'selected' : '' }}>Night</option>
                    </select>
                    <input name="email" type="email" value="{{ $worker->email }}" class="border p-2 w-full rounded" placeholder="Email">
                    <input name="phone" value="{{ $worker->phone }}" class="border p-2 w-full rounded" placeholder="Phone">
                    <textarea name="address" class="border p-2 w-full rounded" placeholder="Address" rows="2">{{ $worker->address }}</textarea>
                </div>
                <div class="flex gap-2 mt-4">
                    <button type="submit" class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded">Update Worker</button>
                    <button type="button" onclick="toggleEditWorkerModal('{{ $worker->id }}')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    @endforeach
</div>

<script>
    function toggleEditModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
    }

    function toggleAddWorkerModal() {
        const modal = document.getElementById('add-worker');
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
    }

    function toggleMoveWorkersModal() {
        const modal = document.getElementById('move-workers');
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
    }

    function toggleEditWorkerModal(workerId) {
        const modal = document.getElementById('edit-worker-' + workerId);
        modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
    }

    // Close modals on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('[id^="edit-"], [id^="add-"], [id^="move-"]');
            modals.forEach(modal => {
                modal.style.display = 'none';
            });
        }
    });
</script>
@endsection
