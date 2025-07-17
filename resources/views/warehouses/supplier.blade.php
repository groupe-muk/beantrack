@extends('layouts.main-view')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-coffee-brown flex items-center gap-2">
            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a1 1 0 001 1h16a1 1 0 001-1V7m-9 4v4m4-4v4m-8-4v4M5 7h14l-1.447-2.894A1 1 0 0016.618 3H7.382a1 1 0 00-.895.553L5 7z" />
            </svg>
            My Warehouses
        </h1>
        <p class="mt-1 text-coffee-brown">Manage your supplier warehouses and workforce</p>
    </div>

    <div class="flex justify-end mb-4 gap-2">
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
                <form action="{{ route('supplier.warehouses.upload.workers') }}" method="POST" enctype="multipart/form-data">
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
                <form action="{{ route('supplier.warehouses.store') }}" method="POST">
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

    <div class="grid grid-cols-3 gap-4 mb-6 text-white">
        <div class="bg-light-brown p-4 rounded-lg">
            <h2 class="text-xl font-bold">Total Warehouses</h2>
            <p>{{ $warehouses->count() }}</p>
        </div>
        <div class="bg-coffee-brown p-4 rounded-lg">
            <h2 class="text-xl font-bold">Total Workers</h2>
            <p>{{ $warehouses->sum(fn($w) => $w->workers->count()) }}</p>
        </div>
        <div class="bg-light-brown p-4 rounded-lg">
            <h2 class="text-xl font-bold">Total Capacity</h2>
            <p>{{ number_format($warehouses->sum('capacity')) }} units</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($warehouses as $warehouse)
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-bold">{{ $warehouse->name }}</h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="document.getElementById('edit-warehouse-{{ $warehouse->id }}').showModal()" class="text-coffee-brown hover:underline text-sm flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z" />
                        </svg>
                        Edit
                    </button>
                    <form method="POST" action="{{ route('supplier.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Delete this warehouse?')">
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25s-7.5-4.108-7.5-11.25a7.5 7.5 0 1115 0z"/>
                </svg>
                {{ $warehouse->location }}
            </p>

            <p class="text-sm text-brown-600">
                <svg class="inline w-4 h-4 text-gray-600 mr-1" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.25a8.25 8.25 0 0115 0v.75H4.5v-.75z"/>
                </svg>
                Manager: {{ $warehouse->manager_name }}
            </p>

            <div class="mb-3 mt-3">
                <div class="flex justify-between items-center text-sm text-brown-600 font-medium mb-1">
                    <span>Capacity: {{ number_format($warehouse->capacity) }} units</span>
                </div>
            </div>

            <h4 class="font-semibold mb-2 text-coffee-brown">Workers ({{ $warehouse->workers->count() }})</h4>
            @foreach ($warehouse->workers as $worker)
            <div class="flex justify-between items-center bg-gray-50 p-2 mb-1 rounded">
                <div>
                    <p class="font-medium">{{ $worker->name }}</p>
                    <p class="text-sm text-gray-600">
                        {{ $worker->role }} • {{ $worker->shift }}
                    </p>
                </div>
                <button onclick="document.getElementById('edit-worker-{{ $worker->id }}').showModal()" class="text-sm text-brown-500 hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z"/></svg> Edit
                </button>
            </div>
            @endforeach

            <form action="{{ route('supplier.warehouses.workers.store', $warehouse) }}" method="POST" class="mt-3">
                @csrf
                <input name="name" placeholder="Name" required class="border p-1 text-sm w-full mb-2">
                <input name="role" placeholder="Role" required class="border p-1 text-sm w-full mb-2">
                <input name="shift" placeholder="Shift" required class="border p-1 text-sm w-full mb-2">
                <button class="bg-coffee-brown hover:bg-light-brown text-white px-2 py-1 rounded text-sm flex items-center gap-1" type="submit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Add Worker
                </button>
            </form>
        </div>

        <!-- Edit warehouse modal -->
        <dialog id="edit-warehouse-{{ $warehouse->id }}" class="p-6 rounded bg-white shadow-lg max-w-md">
            <form method="POST" action="{{ route('supplier.warehouses.update', $warehouse) }}">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold mb-4">Edit Warehouse</h3>
                <input name="name" value="{{ $warehouse->name }}" class="border p-2 w-full mb-2" placeholder="Name">
                <input name="location" value="{{ $warehouse->location }}" class="border p-2 w-full mb-2" placeholder="Location">
                <input name="manager_name" value="{{ $warehouse->manager_name }}" class="border p-2 w-full mb-2" placeholder="Manager">
                <input name="capacity" type="number" value="{{ $warehouse->capacity }}" class="border p-2 w-full mb-4" placeholder="Capacity">
                <div class="flex justify-between">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
                    <button type="button" onclick="this.closest('dialog').close()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </dialog>

        <!-- Edit worker modals -->
        @foreach ($warehouse->workers as $worker)
        <dialog id="edit-worker-{{ $worker->id }}" class="p-6 rounded bg-white shadow-lg max-w-md">
            <form method="POST" action="{{ route('supplier.warehouses.workers.update', $worker) }}">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold mb-4">Edit Worker</h3>
                <input name="name" value="{{ $worker->name }}" class="border p-2 w-full mb-2" placeholder="Name">
                <input name="role" value="{{ $worker->role }}" class="border p-2 w-full mb-2" placeholder="Role">
                <input name="shift" value="{{ $worker->shift }}" class="border p-2 w-full mb-4" placeholder="Shift">
                <div class="flex justify-between">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Save</button>
                    <button type="button" onclick="this.closest('dialog').close()" class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </dialog>
        @endforeach
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

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const warehouseForm = document.getElementById('warehouseForm');
            const uploadForm = document.getElementById('uploadWorkersForm');
            
            if (warehouseForm && !warehouseForm.classList.contains('hidden')) {
                toggleForm();
            }
            if (uploadForm && !uploadForm.classList.contains('hidden')) {
                toggleUploadForm();
            }
        }
    });
</script>
@endsection
