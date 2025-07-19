@extends('layouts.main-view')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-coffee-brown flex items-center gap-2">
            Warehouse Management
        </h1>
        <p class="mt-1 text-coffee-brown">Manage your supplier warehouses and workforce distribution</p>
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
    <div id="uploadWorkersForm" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-2xl w-full shadow-xl">
            <h2 class="text-xl font-bold mb-4">Upload Workers</h2>
            
            <!-- Excel Format Instructions -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-semibold text-blue-800 mb-2">Excel Sheet Format Required:</h3>
                <p class="text-sm text-blue-700 mb-3">Your Excel file should contain the following columns in this exact order:</p>
                
                <div class="grid grid-cols-2 gap-4 text-xs">
                    <div>
                        <strong class="text-blue-800">Column A: name</strong>
                        <p class="text-blue-600">Worker's full name (e.g., John Smith)</p>
                    </div>
                    <div>
                        <strong class="text-blue-800">Column B: role</strong>
                        <p class="text-blue-600">Job role (e.g., Operator, Supervisor, Quality Control)</p>
                    </div>
                    <div>
                        <strong class="text-blue-800">Column C: shift</strong>
                        <p class="text-blue-600">Work shift (Morning, Afternoon, or Night)</p>
                    </div>
                    <div>
                        <strong class="text-blue-800">Column D: email</strong>
                        <p class="text-blue-600">Email address (optional)</p>
                    </div>
                    <div>
                        <strong class="text-blue-800">Column E: phone</strong>
                        <p class="text-blue-600">Phone number (optional)</p>
                    </div>
                    <div>
                        <strong class="text-blue-800">Column F: address</strong>
                        <p class="text-blue-600">Home address (optional)</p>
                    </div>
                </div>
                
                <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded">
                    <p class="text-xs text-yellow-800"><strong>Note:</strong> Workers will be randomly assigned to available warehouses. Make sure the first row contains column headers exactly as shown above.</p>
                </div>
            </div>
            
            <form action="{{ route('supplier.warehouses.upload.workers') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block mb-2 font-medium">Select Excel File (.xlsx, .xls, .csv)</label>
                    <input type="file" name="worker_file" accept=".xlsx,.xls,.csv" required class="border border-gray-300 w-full rounded focus:border-blue-500 focus:outline-none">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-light-brown hover:bg-brown text-white px-6 py-2 rounded transition-colors">Upload Workers</button>
                    <button type="button" onclick="toggleUploadForm()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Remove Workers Modal -->
    <div id="removeWorkersModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-4xl w-full shadow-xl max-h-[80vh] overflow-y-auto">
            <h2 class="text-xl font-bold mb-4 text-red-600">Remove Workers</h2>
            
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700"><strong>Warning:</strong> This action will permanently delete the selected workers. This cannot be undone.</p>
            </div>

            <form action="{{ route('supplier.warehouses.workers.bulk.delete') }}" method="POST" id="removeWorkersForm">
                @csrf
                @method('DELETE')
                
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="select-all-remove" class="mr-2 rounded">
                        <span class="font-medium text-gray-700">Select All Workers</span>
                    </label>
                </div>
                
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @foreach ($warehouses as $warehouse)
                        @if($warehouse->workers->count() > 0)
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-800 mb-3">{{ $warehouse->name }} - {{ $warehouse->location }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach ($warehouse->workers as $worker)
                                        <label class="flex items-center gap-3 p-2 border rounded hover:bg-gray-50 cursor-pointer">
                                            <input type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" class="worker-checkbox rounded">
                                            <div class="flex-1">
                                                <div class="font-medium">{{ $worker->name }}</div>
                                                <div class="text-sm text-gray-600">{{ $worker->role }} - {{ $worker->shift }} Shift</div>
                                                @if($worker->email)
                                                    <div class="text-xs text-gray-500">{{ $worker->email }}</div>
                                                @endif
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach

                    @if($warehouses->sum(fn($w) => $w->workers->count()) == 0)
                        <div class="text-center py-8 text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-lg font-medium">No workers found</p>
                            <p class="text-sm">Add some workers first before removing them.</p>
                        </div>
                    @endif
                </div>
                
                <div class="flex gap-2 mt-6">
                    <button type="submit" id="removeSelectedBtn" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded transition-colors" disabled>Remove Selected</button>
                    <button type="button" onclick="toggleRemoveWorkersModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Warehouse Modal -->
    <div id="warehouseForm" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            <h2 class="text-xl font-bold mb-4">Add New Warehouse</h2>
            <form action="{{ route('supplier.warehouses.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <input name="name" placeholder="Name" required class="border p-2 rounded">
                    <input name="location" placeholder="Location" required class="border p-2 rounded">
                    <input name="manager_name" placeholder="Manager" required class="border p-2 rounded">
                    <input name="capacity" placeholder="Capacity" required type="number" class="border p-2 rounded">
                </div>
                <div class="flex gap-2 mt-3">
                    <button class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded" type="submit">Add Warehouse</button>
                    <button type="button" onclick="toggleForm()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
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
            :value="number_format($warehouses->sum('capacity')) . ' kgs'"
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
                    <p class="text-gray-600">Capacity: {{ number_format($warehouse->capacity) }} kgs</p>
                </div>
                <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                    <button type="button" onclick="toggleEditModal('edit-warehouse-{{ $warehouse->id }}')" class="text-coffee-brown hover:underline text-sm flex items-center gap-1 transition-colors duration-200 hover:text-light-brown">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z" />
                        </svg>
                        Edit
                    </button>
                    <form method="POST" action="{{ route('supplier.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Delete this warehouse?')" class="inline">
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
                <form method="POST" action="{{ route('supplier.warehouses.update', $warehouse) }}">
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
function toggleUploadForm() {
    const form = document.getElementById('uploadWorkersForm');
    form.style.display = form.style.display === 'none' ? 'flex' : 'none';
}

function toggleRemoveWorkersModal() {
    const modal = document.getElementById('removeWorkersModal');
    if (!modal) {
        console.error('Remove workers modal not found');
        return;
    }
    
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'flex';
    } else {
        modal.style.display = 'none';
        // Reset form when closing
        const form = document.getElementById('removeWorkersForm');
        if (form) {
            form.reset();
        }
        if (typeof updateRemoveButton === 'function') {
            updateRemoveButton();
        }
    }
}

function toggleForm() {
    const form = document.getElementById('warehouseForm');
    form.style.display = form.style.display === 'none' ? 'flex' : 'none';
}

function toggleEditModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}

function viewWarehouseDetails(warehouseId) {
    console.log('Attempting to navigate to warehouse:', warehouseId);
    // Use Laravel's route helper to generate the proper URL
    const url = `{{ url('/warehouses') }}/${warehouseId}`;
    console.log('URL:', url);
    // Navigate to warehouse details page
    window.location.href = url;
}

// Remove workers functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-remove');
    const workerCheckboxes = document.querySelectorAll('.worker-checkbox');
    const removeButton = document.getElementById('removeSelectedBtn');
    const removeForm = document.getElementById('removeWorkersForm');

    // Select all functionality
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            workerCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateRemoveButton();
        });
    }

    // Individual checkbox change
    workerCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectAllState();
            updateRemoveButton();
        });
    });

    // Form submission with confirmation
    if (removeForm) {
        removeForm.addEventListener('submit', function(e) {
            const checkedBoxes = document.querySelectorAll('.worker-checkbox:checked');
            if (checkedBoxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one worker to remove.');
                return;
            }
            
            const workerNames = Array.from(checkedBoxes).map(cb => {
                const label = cb.closest('label');
                return label.querySelector('.font-medium').textContent;
            });
            
            const confirmMessage = `Are you sure you want to remove ${checkedBoxes.length} worker(s)?\n\n${workerNames.join('\n')}\n\nThis action cannot be undone.`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    }

    function updateSelectAllState() {
        if (!selectAllCheckbox) return;
        
        const checkedCount = document.querySelectorAll('.worker-checkbox:checked').length;
        const totalCount = workerCheckboxes.length;
        
        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === totalCount) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    function updateRemoveButton() {
        if (!removeButton) return;
        
        const checkedCount = document.querySelectorAll('.worker-checkbox:checked').length;
        removeButton.disabled = checkedCount === 0;
        removeButton.textContent = checkedCount > 0 ? `Remove Selected (${checkedCount})` : 'Remove Selected';
    }

    // Make functions globally available
    window.updateRemoveButton = updateRemoveButton;
    window.updateSelectAllState = updateSelectAllState;
});
</script>
@endsection
