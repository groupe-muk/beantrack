@extends('layouts.main-view')

@section('content')
<div class="p-5 bg-light-background">
  <!-- Header Section -->
  <h1 class="text-3xl font-bold text-dashboard-light">Inventory Management</h1>
  <p class="text-soft-brown mb-4">Manage your coffee stock and track availability</p>

  @if (session('success'))
    <div class="bg-status-background-green border border-progress-bar-green text-status-text-green px-4 py-3 rounded relative mb-4" role="alert">
      <strong class="font-bold">Success!</strong>
      <span class="block sm:inline">{{ session('success') }}</span>
    </div>
  @endif

  @if ($errors->any())
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
      <strong class="font-bold">Error!</strong>
      <ul class="mt-2 list-disc list-inside">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Stats Section -->
  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-{{ min(count($coffeeTypes) + 1, 4) }} gap-4 mb-8">
    @foreach($coffeeTypes as $coffeeType)
      <x-stats-card
        title="{{ $coffeeType }}"
        :value="number_format($coffeeTypeQuantities[$coffeeType] ?? 0, 2)"
        unit="kg"
        iconClass="fa-cube"
      />
    @endforeach
    <x-stats-card
      title="Total Coffee"
      :value="number_format($totalQuantity, 2)"
      unit="kg"
      iconClass="fa-cubes"
    />
  </div>

  <!-- Inventory Table -->
  <div class="mt-10">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-dashboard-light">Inventory Items</h2>
      <div class="flex space-x-2">
        <button class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-light-brown transition-colors" data-modal-open="createRawCoffeeModal">+ Create New Coffee Type</button>
        <button class="bg-light-brown text-white px-4 py-2 rounded hover:bg-coffee-brown transition-colors" data-modal-open="addRawCoffeeModal">+ Add to Inventory</button>
      </div>
    </div>

    <div class="bg-white rounded shadow overflow-x-auto">
      <table class="min-w-full leading-normal">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-5 py-3 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">SKU</th>
            <th class="px-5 py-3 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Product Name</th>
            <th class="px-5 py-3 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Total Quantity</th>
            <th class="px-5 py-3 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-5 py-3 border-b border-gray-200 text-left text-sm font-semibold text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($inventoryItems as $item)
            <tr class="hover:bg-gray-50">
              <td class="px-5 py-3 border-b border-gray-200 text-sm">
                {{ $loop->iteration }}
              </td>
              <td class="px-5 py-3 border-b border-gray-200 text-sm">
                {{ $item->name }}
              </td>
              <td class="px-5 py-3 border-b border-gray-200 text-sm">
                {{ number_format($item->total_quantity, 2) }} kg
              </td>
              <td class="px-5 py-3 border-b border-gray-200 text-sm">
                @if ($item->total_quantity > 10)
                  <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">In Stock</span>
                @else
                  <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Low Stock</span>
                @endif
              </td>
              <td class="px-5 py-3 border-b border-gray-200 text-sm">
                <button 
                  type="button"
                  class="text-blue-600 hover:text-blue-900 mr-3 view-details-btn"
                  data-type="raw-coffee"
                  data-coffee-type="{{ $item->name }}"
                  data-current-grade="All">
                  View Details
                </button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-3 border-b border-gray-200 text-sm text-center text-gray-500">
                No items found.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>





<x-modal 
    id="addRawCoffeeModal" 
    title="Add New Raw Coffee Item" 
    size="md" 
    submit-form="addRawCoffeeForm" 
    submit-text="Add Item"
    cancel-text="Cancel">
    
    <form action="{{ route('supplierInventory.store') }}" method="POST" id="addRawCoffeeForm">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="">
        
        {{-- Name Field --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Product Name
            </label>
            <select name="raw_coffee_id" id="raw-coffee-name" required>
              <option value="">Select Raw Coffee Item</option>
              @foreach($rawCoffeeItems as $rawCoffeeItem)
              <option value="{{ $rawCoffeeItem->id }}" data-grade="{{ $rawCoffeeItem->grade }}">
                {{ $rawCoffeeItem->coffee_type }} - Grade {{ $rawCoffeeItem->grade }}
              </option>
              @endforeach
            </select>
            @error('raw_coffee_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Grade Field --}}
        <div class="mb-4">
            <label for="rawCoffeeGrade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Grade
            </label>
            <input type="text" 
                id="rawCoffeeGrade" 
                name="grade" 
                readonly
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md 
                       bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 
                       cursor-not-allowed"
                placeholder="Select raw coffee to see grade"
                value="{{ old('grade') }}">
            @error('grade')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Quantity Field --}}
        <div class="mb-4">
            <label for="rawCoffeeQuantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Quantity
            </label>
            <input type="number" 
                id="rawCoffeeQuantity" 
                name="quantity_in_stock" 
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="Enter Quantity"
                value="{{ old('quantity_in_stock') }}">
            @error('quantity_in_stock')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Defect Count Field --}}
        <div class="mb-4">
            <label for="rawCoffeeDefectCount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Defect Count (per 100g sample)
            </label>
            <input type="number" 
                id="rawCoffeeDefectCount" 
                name="defect_count"
                min="0"
                step="1" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="Enter defect count (leave empty to keep existing)"
                value="{{ old('defect_count') }}">
            @error('defect_count')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Only applies when creating new coffee grades. Leave empty to keep existing defect count.
            </p>
        </div>

        {{-- Warehouse Field --}}
        <div class="mb-4">
            <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Warehouse
            </label>
            <select name="supply_center_id" id="coffeeProductWarehouse" required>
              <option value="">Select Warehouse</option>
              @foreach($warehouses as $warehouse)
              <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
              @endforeach
            </select>
            @error('supply_center_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-modal> 

<!-- View Details Modal -->
<x-modal id="viewDetailsModal"
 title="Raw Coffee Details" 
 size="xl"
 submit-form=""
  cancel-text="Close"
  >
  <div id="itemDetailsContent">
    <!-- Content will be loaded dynamically -->
    </div>

  
  
</x-modal>

<!-- Create New Raw Coffee Modal -->
<x-modal 
    id="createRawCoffeeModal" 
    title="Create New Raw Coffee Type" 
    size="md" 
    submit-form="createRawCoffeeForm" 
    submit-text="Create Coffee Type"
    cancel-text="Cancel">
    
    <form action="{{ route('supplierInventory.createRawCoffee') }}" method="POST" id="createRawCoffeeForm">
        @csrf
        
        {{-- Coffee Type Field --}}
        <div class="mb-4">
            <label for="coffee_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Coffee Type *
            </label>
            <input type="text" 
                id="coffee_type" 
                name="coffee_type" 
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="e.g., Ethiopian Sidamo, Colombian Supremo"
                value="{{ old('coffee_type') }}">
            @error('coffee_type')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Grade Field --}}
        <div class="mb-4">
            <label for="grades" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Grades * (Select multiple)
            </label>
            <div class="space-y-2 bg-gray-50 dark:bg-gray-800 p-3 rounded-md">
                <label class="flex items-center">
                    <input type="checkbox" name="grades[]" value="A" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('A', old('grades', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Grade A (Premium)</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="grades[]" value="B" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('B', old('grades', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Grade B (High Quality)</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="grades[]" value="C" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('C', old('grades', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Grade C (Standard)</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="grades[]" value="AA" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('AA', old('grades', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Grade AA (Specialty)</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="grades[]" value="Premium" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('Premium', old('grades', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Premium</span>
                </label>
            </div>
            @error('grades')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Select all grades available for this coffee type. Each grade can be managed separately in inventory.
            </p>
        </div>

        {{-- Screen Size Field --}}
        <div class="mb-4">
            <label for="screen_size" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Screen Size
            </label>
            <input type="text" 
                id="screen_size" 
                name="screen_size"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="e.g., 16/18, 14/16"
                value="{{ old('screen_size') }}">
            @error('screen_size')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Defect Count Field --}}
        <div class="mb-4">
            <label for="defect_count" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Defect Count (per 100g sample)
            </label>
            <input type="number" 
                id="defect_count" 
                name="defect_count"
                min="0"
                step="1"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="Enter defect count"
                value="{{ old('defect_count', 0) }}">
            @error('defect_count')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-modal>

@endsection

@push('styles')
<style>
  /* Hide number input spinners */
  input[type="number"] {
    -moz-appearance: textfield;
  }
  input[type="number"]::-webkit-outer-spin-button,
  input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Supplier inventory page loaded');
    
    // Setup modal triggers
    document.querySelectorAll('[data-modal-open]').forEach(button => {
        const modalId = button.getAttribute('data-modal-open');
        console.log('Button found with modal target:', modalId);
        const modal = document.getElementById(modalId);
        
        if (!modal) {
            console.error(`Modal with ID ${modalId} not found!`);
            return;
        }
        
        button.addEventListener('click', () => {
            console.log('Opening modal:', modalId);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    });

    // Setup modal close buttons
    document.querySelectorAll('.close-modal, [data-modal-close]').forEach(button => {
        button.addEventListener('click', event => {
            const modal = event.target.closest('.fixed');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        });
    });
    
    // Close modals when clicking outside content
    document.querySelectorAll('.modal-body').forEach(modal => {
        modal.addEventListener('click', event => {
            if (event.target === modal) {
                modal.closest('.fixed').classList.add('hidden');
                modal.closest('.fixed').classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        });
    });

    // Handle Add Raw Coffee Modal reset
    const addRawCoffeeBtn = document.querySelector('[data-modal-open="addRawCoffeeModal"]');
    if (addRawCoffeeBtn) {
        addRawCoffeeBtn.addEventListener('click', () => {
            // Reset the form
            const form = document.getElementById('addRawCoffeeForm');
            if (form) {
                form.reset();
                
                // Reset form method to POST
                const methodInput = document.getElementById('form-method');
                if (methodInput) methodInput.value = '';
                
                // Reset form action
                form.action = "{{ route('supplierInventory.store') }}";
                
                // Reset modal title
                const modalTitle = document.getElementById('addRawCoffeeModal').querySelector('.modal-title');
                if (modalTitle) {
                    modalTitle.textContent = 'Add New Raw Coffee Item';
                }
                
                // Reset submit button text
                const submitButton = document.getElementById('addRawCoffeeModal').querySelector('.modal-submit-button');
                if (submitButton) {
                    submitButton.textContent = 'Add Item';
                }
            }
        });
    }

    // Handle Create Raw Coffee Modal
    const createRawCoffeeBtn = document.querySelector('[data-modal-open="createRawCoffeeModal"]');
    const createRawCoffeeModal = document.getElementById('createRawCoffeeModal');
    
    if (createRawCoffeeBtn && createRawCoffeeModal) {
        createRawCoffeeBtn.addEventListener('click', function() {
            // Reset form
            const form = document.getElementById('createRawCoffeeForm');
            if (form) form.reset();
            
            // Show modal
            createRawCoffeeModal.classList.remove('hidden');
            createRawCoffeeModal.classList.add('flex');
        });
    }

    // Add validation for raw coffee form submission
    const rawCoffeeForm = document.getElementById('createRawCoffeeForm');
    if (rawCoffeeForm) {
        rawCoffeeForm.addEventListener('submit', function(e) {
            const checkboxes = rawCoffeeForm.querySelectorAll('input[name="grades[]"]:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one grade for the raw coffee.');
                return false;
            }
        });
    }

    // Auto-populate grade when raw coffee is selected in inventory form
    const rawCoffeeSelect = document.getElementById('raw-coffee-name');
    const gradeInput = document.getElementById('rawCoffeeGrade');
    
    if (rawCoffeeSelect && gradeInput) {
        rawCoffeeSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const grade = selectedOption.getAttribute('data-grade');
            
            if (grade) {
                gradeInput.value = grade;
            } else {
                gradeInput.value = '';
            }
        });
    }

    // Setup View Details functionality
    console.log('Setting up view details buttons...');
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    console.log('Found view details buttons:', viewDetailsButtons.length);
    
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    const itemDetailsContent = document.getElementById('itemDetailsContent');
    
    viewDetailsButtons.forEach(btn => {
        console.log('Setting up event listener for button:', btn);
        btn.addEventListener('click', function() {
            console.log('View details button clicked');
            const coffeeType = btn.getAttribute('data-coffee-type');
            console.log('Coffee type:', coffeeType);
            
            if (!coffeeType) {
                console.error('No coffee type found for button');
                return;
            }
            
            // Show loading state
            itemDetailsContent.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading details...</p></div>';
            
            // Open modal
            viewDetailsModal.classList.remove('hidden');
            viewDetailsModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            
            // Fetch details from the server
            fetch(`/supplierInventory/details/${encodeURIComponent(coffeeType)}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Fetched data:', data);
                    
                    let content = `
                        <div class="space-y-6">
                            <!-- Total Quantity Section -->
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="text-xl font-semibold text-gray-900">${data.coffee_type || coffeeType}</h3>
                                <div class="mt-2">
                                    <div class="text-3xl font-bold text-light-brown mt-1">${data.total_quantity || 0} kg</div>
                                </div>
                            </div>
                            
                            <!-- Grade Breakdown -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-3">Grade Breakdown</h4>
                                ${data.grade_breakdown && data.grade_breakdown.length > 0 ? `
                                    <div class="space-y-4">
                                        ${data.grade_breakdown.map(grade => `
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h5 class="text-lg font-medium text-gray-900">Grade: ${grade.grade}</h5>
                                                    <span class="text-xl font-bold text-light-brown">${grade.total_quantity} kg</span>
                                                </div>
                                                
                                                ${grade.warehouse_details && grade.warehouse_details.length > 0 ? `
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                ${grade.warehouse_details.map(detail => `
                                                                    <tr>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">${detail.warehouse}</td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">${detail.quantity} kg</td>
                                                                        <td class="px-4 py-2 text-sm text-gray-500">${detail.last_updated}</td>
                                                                        <td class="px-4 py-2 text-sm">
                                                                            <div class="flex space-x-2">
                                                                                <button 
                                                                                    type="button"
                                                                                    onclick="editInventoryItem('${detail.id}')" 
                                                                                    class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                                                                    Edit
                                                                                </button>
                                                                                <button 
                                                                                    type="button"
                                                                                    onclick="confirmDeleteItem('${detail.id}')"
                                                                                    class="text-red-600 hover:text-red-900 text-sm font-medium">
                                                                                    Delete
                                                                                </button>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                `).join('')}
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                ` : `
                                                    <p class="text-gray-500 text-center py-4">No inventory found for this grade</p>
                                                `}
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : `
                                    <div class="text-center py-8 text-gray-500">
                                        <p>No inventory records found for this coffee type.</p>
                                    </div>
                                `}
                            </div>
                        </div>
                    `;
                    
                    itemDetailsContent.innerHTML = content;
                })
                .catch(error => {
                    console.error('Error fetching details:', error);
                    itemDetailsContent.innerHTML = '<div class="text-center py-8 text-red-600"><p>Error loading details. Please try again.</p></div>';
                });
        });
    });
});

// Edit inventory item function
function editInventoryItem(itemId) {
    console.log('Editing inventory item:', itemId);
    
    // Show loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
    loadingOverlay.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><i class="fas fa-spinner fa-spin text-light-brown mr-2"></i> Loading...</div>';
    document.body.appendChild(loadingOverlay);
    
    // Close the view details modal
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    viewDetailsModal.classList.add('hidden');
    viewDetailsModal.classList.remove('flex');
    
    // Fetch the inventory item data
    fetch(`/supplierInventory/item/${itemId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Item data for editing:', data);
            
            // Remove loading overlay
            document.body.removeChild(loadingOverlay);
            
            // Open the edit modal (reusing the add modal)
            const editModal = document.getElementById('addRawCoffeeModal');
            
            // Set the modal title
            const modalTitle = editModal.querySelector('.modal-title');
            if (modalTitle) {
                modalTitle.textContent = 'Edit Inventory Item';
            }
            
            // Change the submit button text
            const submitButton = editModal.querySelector('.modal-submit-button');
            if (submitButton) {
                submitButton.textContent = 'Save Changes';
            }
            
            // Populate the form with the item's data
            const form = document.getElementById('addRawCoffeeForm');
            
            // Set form method to PUT for update
            const methodInput = document.getElementById('form-method');
            if (methodInput) methodInput.value = 'PUT';
            
            // Set form action to update endpoint
            form.action = `/supplierInventory/item/${itemId}`;
            
            // Populate form fields
            if (data.raw_coffee_id) {
                const rawCoffeeSelect = document.getElementById('raw-coffee-name');
                if (rawCoffeeSelect) rawCoffeeSelect.value = data.raw_coffee_id;
            }
            
            const gradeInput = document.getElementById('rawCoffeeGrade');
            if (gradeInput) gradeInput.value = data.grade || '';
            
            const quantityInput = document.getElementById('rawCoffeeQuantity');
            if (quantityInput) quantityInput.value = data.quantity_in_stock || '';
            
            if (data.supply_center_id) {
                const warehouseSelect = document.getElementById('coffeeProductWarehouse');
                if (warehouseSelect) warehouseSelect.value = data.supply_center_id;
            }
            
            // Show the edit modal
            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            // Remove loading overlay
            if (document.body.contains(loadingOverlay)) {
                document.body.removeChild(loadingOverlay);
            }
            
            console.error('Error fetching item data:', error);
            alert('Error loading item data. Please try again.');
        });
}

// Delete inventory item function
function confirmDeleteItem(itemId) {
    console.log('Attempting to delete inventory item:', itemId);
    
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        // Show loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
        loadingOverlay.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><i class="fas fa-spinner fa-spin text-light-brown mr-2"></i> Deleting...</div>';
        document.body.appendChild(loadingOverlay);
        
        // Create and submit a form to delete the item
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/supplierInventory/item/${itemId}`;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush