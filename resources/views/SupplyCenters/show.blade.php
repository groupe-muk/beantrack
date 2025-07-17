@extends('layouts.main-view')

@section('content')
<div class="bg-coffee-light min-h-screen p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-3xl font-bold text-coffee-brown">{{ $supplycenter->name }}</h1>
                <p class="text-gray-600 pt-4">Supply Center Details</p>
            </div>    

            <div class="flex gap-2">
   
                  <a href="{{ route('supplycenters') }}" class="text-white transition-colors duration-200 flex items-center gap-4 bg-light-brown rounded px-4 py-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <p class="text-white">Back to Supply Centers</p>
                </a>
                
                <button onclick="toggleEditModal('edit-supplycenter')" class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-dashboard-light transition-colors duration-200">
                    Edit Supply Center
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

        <!-- Supply Center Info Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Location</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ $supplycenter->location }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Manager</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ $supplycenter->manager }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Capacity</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ number_format($supplycenter->capacity) }} kgs</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="text-sm font-semibold text-gray-600 uppercase">Total Workers</h3>
                <p class="text-xl font-bold text-coffee-brown">{{ $supplycenter->workers->count() }}</p>
            </div>
        </div>

        <!-- Workers Section -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-bold text-coffee-brown">Workers</h2>
                <div class="flex gap-2">
                    @if($supplycenter->workers->count() > 0)
                        <button onclick="toggleMoveWorkersModal()" class="bg-light-brown text-white px-4 py-2 rounded hover:bg-brown transition-colors duration-200">
                            Move Workers
                        </button>
                    @endif
                    <button onclick="toggleAddWorkerModal()" class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-dashboard-light transition-colors duration-200">
                        Add Worker
                    </button>
                </div>
            </div>

            @if($supplycenter->workers->count() > 0)
                <!-- Workers by Shift -->
                @php
                    $workersByShift = $supplycenter->workers->groupBy('shift');
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
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-semibold text-coffee-brown">{{ $worker->name }}</h4>
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs bg-coffee-brown text-white px-2 py-1 rounded">{{ $worker->shift }}</span>

                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 mb-1"><strong>Role:</strong> {{ $worker->role }}</p>
                                        <p class="text-sm text-gray-600 mb-1"><strong>Email:</strong> {{ $worker->email }}</p>
                                        <p class="text-sm text-gray-600 mb-1"><strong>Phone:</strong> {{ $worker->phone }}</p>
                                        <p class="text-sm text-gray-600"><strong>Address:</strong> {{ $worker->address }}</p>
                                        <div class="flex w-full justify-end">
                                            <button onclick="openEditWorkerModal('{{ $worker->id }}', '{{ addslashes($worker->name) }}', '{{ addslashes($worker->role) }}', '{{ addslashes($worker->shift) }}', '{{ addslashes($worker->email ?? '') }}', '{{ addslashes($worker->phone ?? '') }}', '{{ addslashes($worker->address ?? '') }}')" 
                                                class="bg-light-brown text-white p-1 rounded flex cursor-pointer text-xs items-center" title="Edit Worker">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3.536-3.536M2.5 20.5l1.5-6.5 6.5 6.5-6.5 1.5z" />
                                                    </svg>
                                                Edit 
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-600 mb-2">No Workers Assigned</h3>
                    <p class="text-gray-500 mb-4">This supply center doesn't have any workers yet.</p>
                    <button onclick="toggleAddWorkerModal()" class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-light-brown transition-colors duration-200">
                        Add First Worker
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Edit Supply Center Modal -->
    <div id="edit-supplycenter" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            <form method="POST" action="{{ route('supplycenters.update', $supplycenter) }}">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold mb-4">Edit Supply Center</h3>
                <div class="grid gap-3">
                    <input name="name" value="{{ $supplycenter->name }}" class="border p-2 w-full rounded" placeholder="Name" required>
                    <input name="location" value="{{ $supplycenter->location }}" class="border p-2 w-full rounded" placeholder="Location" required>
                    <input name="manager" value="{{ $supplycenter->manager }}" class="border p-2 w-full rounded" placeholder="Manager" required>
                    <input name="capacity" value="{{ $supplycenter->capacity }}" class="border p-2 w-full rounded" placeholder="Capacity" type="number" required>
                </div>
                <div class="flex gap-2 mt-4">
                    <button class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded" type="submit">Update</button>
                    <button type="button" onclick="toggleEditModal('edit-supplycenter')" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Worker Modal -->
    <div id="add-worker-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            <form method="POST" action="{{ route('worker.store', $supplycenter) }}">
                @csrf
                <h3 class="text-lg font-bold mb-4">Add New Worker</h3>
                <div class="grid gap-3">
                    <input name="name" class="border p-2 w-full rounded" placeholder="Worker Name" required>
                    <input name="role" class="border p-2 w-full rounded" placeholder="Role/Position" required>
                    <select name="shift" class="border p-2 w-full rounded" required>
                        <option value="">Select Shift</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Night">Night</option>
                    </select>
                    <input name="email" type="email" class="border p-2 w-full rounded" placeholder="Email (optional)">
                    <input name="phone" class="border p-2 w-full rounded" placeholder="Phone Number (optional)">
                    <textarea name="address" class="border p-2 w-full rounded" placeholder="Address (optional)" rows="2"></textarea>
                </div>
                <div class="flex gap-2 mt-4">
                    <button class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded" type="submit">Add Worker</button>
                    <button type="button" onclick="toggleAddWorkerModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Worker Modal -->
    <div id="edit-worker-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-lg w-full shadow-xl">
            <form method="POST" action="" id="edit-worker-form">
                @csrf
                @method('PATCH')
                <h3 class="text-lg font-bold mb-4">Edit Worker</h3>
                <div class="grid gap-3">
                    <input name="name" id="edit-worker-name" class="border p-2 w-full rounded" placeholder="Worker Name" required>
                    <input name="role" id="edit-worker-role" class="border p-2 w-full rounded" placeholder="Role/Position" required>
                    <select name="shift" id="edit-worker-shift" class="border p-2 w-full rounded" required>
                        <option value="">Select Shift</option>
                        <option value="Morning">Morning</option>
                        <option value="Afternoon">Afternoon</option>
                        <option value="Night">Night</option>
                    </select>
                    <input name="email" id="edit-worker-email" type="email" class="border p-2 w-full rounded" placeholder="Email">
                    <input name="phone" id="edit-worker-phone" class="border p-2 w-full rounded" placeholder="Phone Number">
                    <textarea name="address" id="edit-worker-address" class="border p-2 w-full rounded" placeholder="Address" rows="2"></textarea>
                </div>
                <div class="flex gap-2 mt-4">
                    <button class="bg-coffee-brown hover:bg-light-brown text-white px-4 py-2 rounded" type="submit">Update Worker</button>
                    <button type="button" onclick="toggleEditWorkerModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Move Workers Modal -->
    <div id="move-workers-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center" style="display: none;">
        <div class="bg-white rounded-lg p-6 m-4 max-w-4xl w-full max-h-[90vh] overflow-y-auto shadow-xl">
            <form method="POST" action="{{ route('supplycenters.move.workers', $supplycenter) }}">
                @csrf
                <h3 class="text-lg font-bold mb-4 text-dashboard-light">Move Workers to Another Supply Center</h3>
                
                <!-- Destination Supply Center Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Move to Supply Center:</label>
                    <select name="destination_supply_center_id" required class="w-full border p-2 rounded">
                        <option value="">Select destination supply center</option>
                        @php
                            $otherSupplyCenters = App\Models\SupplyCenter::where('id', '!=', $supplycenter->id)->get();
                        @endphp
                        @foreach($otherSupplyCenters as $center)
                            <option value="{{ $center->id }}">{{ $center->name }} ({{ $center->location }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Workers Selection -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Workers to Move:</label>
                    <div class="border rounded p-3 max-h-60 overflow-y-auto">
                        <div class="mb-2">
                            <label class="flex items-center">
                                <input type="checkbox" id="select-all-workers" class="mr-2">
                                <span class="font-medium">Select All Workers</span>
                            </label>
                        </div>
                        <hr class="mb-3">
                        
                        @foreach(['Morning', 'Afternoon', 'Night'] as $shift)
                            @if(isset($workersByShift[$shift]) && $workersByShift[$shift]->count() > 0)
                                <div class="mb-3">
                                    <h4 class="font-semibold text-coffee-brown mb-2">{{ $shift }} Shift</h4>
                                    @foreach($workersByShift[$shift] as $worker)
                                        <label class="flex items-center mb-1 p-2 hover:bg-gray-50 rounded">
                                            <input type="checkbox" name="worker_ids[]" value="{{ $worker->id }}" class="mr-3 worker-checkbox">
                                            <div class="flex-1">
                                                <span class="font-medium">{{ $worker->name }}</span>
                                                <span class="text-sm text-gray-600 ml-2">({{ $worker->role }})</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button class="bg-light-brown hover:bg-brown text-white px-4 py-2 rounded" type="submit">Move Selected Workers</button>
                    <button type="button" onclick="toggleMoveWorkersModal()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleEditModal(modalId) {
    const modal = document.getElementById(modalId);
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}

function toggleAddWorkerModal() {
    const modal = document.getElementById('add-worker-modal');
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}

function toggleMoveWorkersModal() {
    const modal = document.getElementById('move-workers-modal');
    modal.style.display = modal.style.display === 'none' ? 'flex' : 'none';
}

function toggleEditWorkerModal() {
    console.log('Toggling edit worker modal');
    const modal = document.getElementById('edit-worker-modal');
    if (!modal) {
        console.error('Edit worker modal not found');
        return;
    }
    
    console.log('Current display:', modal.style.display);
    if (modal.style.display === 'none' || modal.style.display === '') {
        modal.style.display = 'flex';
        console.log('Modal shown');
    } else {
        modal.style.display = 'none';
        console.log('Modal hidden');
    }
}

function openEditWorkerModal(workerId, name, role, shift, email, phone, address) {
    console.log('Opening edit modal for worker:', workerId);
    console.log('Worker data:', {workerId, name, role, shift, email, phone, address});
    
    // Set form action with worker ID
    const form = document.getElementById('edit-worker-form');
    if (!form) {
        console.error('Edit worker form not found');
        return;
    }
    form.action = `/worker/${workerId}`;
    
    // Populate form fields
    const nameField = document.getElementById('edit-worker-name');
    const roleField = document.getElementById('edit-worker-role');
    const shiftField = document.getElementById('edit-worker-shift');
    const emailField = document.getElementById('edit-worker-email');
    const phoneField = document.getElementById('edit-worker-phone');
    const addressField = document.getElementById('edit-worker-address');
    
    if (!nameField || !roleField || !shiftField) {
        console.error('Required form fields not found');
        return;
    }
    
    nameField.value = name;
    roleField.value = role;
    shiftField.value = shift;
    emailField.value = email || '';
    phoneField.value = phone || '';
    addressField.value = address || '';
    
    console.log('Form populated, showing modal');
    // Show modal
    toggleEditWorkerModal();
}

// Select all workers functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('select-all-workers');
    const workerCheckboxes = document.querySelectorAll('.worker-checkbox');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            workerCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        
        // Update select all checkbox when individual checkboxes change
        workerCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allChecked = Array.from(workerCheckboxes).every(cb => cb.checked);
                const noneChecked = Array.from(workerCheckboxes).every(cb => !cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = !allChecked && !noneChecked;
            });
        });
    }
});
</script>
@endsection
