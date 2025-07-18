@extends('layouts.main-view')

@section('content')
<div class="p-5 bg-light-background dark:bg-dark-background">

  <!-- Header Section -->
  
  <h1 class="text-3xl font-bold text-dashboard-light">Inventory Management</h1>

  <p class="text-soft-brown mb-4">Track and manage your inventory across all locations</p>

  @if (session('success'))
        <div class="bg-status-background-green border border-progress-bar-green text-status-text-green px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif
    @if (session('info'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Info!</strong>
            <span class="block sm:inline">{{ session('info') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Please check the form below for errors.</span>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
  
  <!-- Stats Section -->
<div class="space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-2 ">
        
        <x-stats-card
            title="Arabica"
            :value="$rawCoffeeArabicaQuantity"
            valueId="raw-coffee-arabica-quantity"
            unit="kg"
            iconClass="fa-cube"
             class="p-3"
            
        />
        <x-stats-card
            title="Robusta"
            :value="$rawCoffeeRobustaQuantity"
            valueId="raw-coffee-robusta-quantity"
            unit="kg"
            iconClass="fa-cube"
            class="p-3"
           
        />
        <x-stats-card
            title="Mountain blend"
            :value="$processedCoffeeMountainBrewQuantity"
            valueId="processed-coffee-mountain-brew-quantity"
            unit="kg"
            iconClass="fa-cube"
             class="p-3"
            
        />
        <x-stats-card
            title="Morning brew"
            :value="$processedCoffeeMorningBrewQuantity"
            valueId="processed-coffee-morning-brew-quantity"
            unit="kg"
            iconClass="fa-cube"
             class="p-3"
            
        />
    </div>
</div>

<!-- Inventory Table -->
  <!-- Raw Coffee Table -->
<div class="mt-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-dashboard-light pb-5">Raw Coffee</h1>
        <button class="bg-light-brown text-white px-4 py-2 rounded"data-modal-open="addRawCoffeeModal">+ Add Item</button>
    </div>
    <div class="bg-white rounded shadow overflow-x-auto p-4">
        <table class="min-w-full leading-normal" id="search-table">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Product Name</th>
                    <th class="px-4 py-2">Total Quantity</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $consolidatedRawCoffee = $rawCoffeeInventory->groupBy('coffee_type')->map(function($items) {
                        return (object)[
                            'id' => $items->first()->id,
                            'coffee_type' => $items->first()->coffee_type,
                            'total_quantity' => $items->sum('total_quantity')
                        ];
                    });
                @endphp
                @forelse ($consolidatedRawCoffee as $rawCoffee)
                    <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors duration-150">
                        <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                            {{ $rawCoffee->id ?? 'N/A' }}
                        </td>
                        <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                            {{ $rawCoffee->coffee_type }}
                        </td>
                        <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                            {{ number_format($rawCoffee->total_quantity, 2) }} kg
                        </td>
                        <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                            @if ($rawCoffee->total_quantity > 10)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">In Stock</span>
                            @elseif ($rawCoffee->total_quantity > 0)
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Low Stock</span>
                            @else
                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Out of Stock</span>
                            @endif
                        </td>
                        <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm">
                            <div class="flex items-center space-x-3">
                                <button 
                                    type="button"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 text-xs cursor-pointer view-details-btn"
                                    data-type="raw-coffee"
                                    data-id="{{ $rawCoffee->id }}">
                                    View Details
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-center text-sm text-gray-500 dark:text-gray-400">
                            No items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Processed Coffee Table -->
<div class="flex justify-between items-center mb-6 pt-10">
    <h1 class="text-2xl font-semibold text-dashboard-light pb-5">Processed Coffee</h1>
    <div>
        <button class="bg-light-brown text-white px-4 py-2 rounded" data-mode="add" data-modal-open="addProcessedCoffeeModal">+ Add Item</button>
    </div>
</div>
<div class="bg-white rounded shadow overflow-x-auto p-4">
    <table class="min-w-full leading-normal" id="search-table2">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="px-4 py-2">SKU</th>
                <th class="px-4 py-2">Product Name</th>
                <th class="px-4 py-2">Total Quantity</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Actions</th>
            </tr>
        </thead>
        <tbody>

            @php
                $consolidatedCoffeeProducts = $coffeeProductInventory->groupBy('name')->map(function($items) {
                    return (object)[
                        'id' => $items->first()->id,
                        'name' => $items->first()->name,
                        'total_quantity' => $items->sum('total_quantity')
                    ];
                });
            @endphp
            @forelse ($consolidatedCoffeeProducts as $coffeeProduct)
                <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors duration-150">
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        {{ $coffeeProduct->id ?? 'N/A' }}
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        {{ $coffeeProduct->name }}
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        {{ number_format($coffeeProduct->total_quantity, 2) }} kg
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        @if ($coffeeProduct->total_quantity > 10)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">In Stock</span>
                        @elseif ($coffeeProduct->total_quantity > 0)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Low Stock</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Out of Stock</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm">
                        <div class="flex items-center space-x-3">
                            <button 
                                type="button"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 text-xs cursor-pointer view-details-btn"
                                data-type="coffee-product"
                                data-id="{{ $coffeeProduct->id }}">
                                View Details
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-center text-sm text-gray-500 dark:text-gray-400">
                        No items found.
                    </td>
                </tr>
            @endforelse

          
        </tbody>
    </table>
</div>
  </div>

<x-modal 
    id="addRawCoffeeModal" 
    title="Add New Raw Coffee Item" 
    size="md" 
    submit-form="addRawCoffeeForm" 
    submit-text="Add Item"
    cancel-text="Cancel">
    
    <form action="{{ route('inventory.store') }}" method="POST" id="addRawCoffeeForm">
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
              <option value="{{ $rawCoffeeItem->id }}">{{ $rawCoffeeItem->coffee_type }}</option>
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
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="Enter coffee grade"
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

        {{-- Warehouse Field --}}
        <div class="mb-4">
            <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Warehouse
            </label>
            <select name="supply_center_id" id="coffeeProductWarehouse" required>
              <option value="">Select Warehouse</option>
              @foreach($supplyCenters as $center)
              <option value="{{ $center->id }}">{{ $center->name }}</option>
              @endforeach
            </select>
            @error('supply_center_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-modal> 

<x-modal 
    id="addProcessedCoffeeModal" 
    title="Add New Processed Coffee Item" 
    size="md" 
    submit-form="addProcessedCoffeeForm" 
    submit-text="Add Item"
    cancel-text="Cancel">
    
    <form action="{{ route('inventory.store') }}" method="POST" id="addProcessedCoffeeForm">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="">

        {{-- Name Field --}}
        <div class="mb-4">
            <label for="coffee_product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Processed Coffee Name
            </label>
            <select name="coffee_product_id" id="coffee-product-name" required>
              <option value="">Select Processed Coffee Item</option>
              @foreach($coffeeProductItems as $coffeeProductItem)
              <option value="{{ $coffeeProductItem->id }}">{{ $coffeeProductItem->name }}</option>
              @endforeach
            </select>
            @error('coffee_product_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category Field --}}
        <div class="mb-4">
            <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Category
            </label>
            <input type="text" 
                   id="coffee-product-category" 
                   name="category" 
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter coffee category"
                   value="{{ old('category') }}">
            @error('category')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Quantity Field --}}
        <div class="mb-4">
            <label for="coffeeProductQuantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Quantity
            </label>
            <input type="number" 
                   id="coffeeProductQuantity" 
                   name="quantity_in_stock" 
                   required
                   min="0"
                   step="0.01"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter quantity"
                   value="{{ old('quantity_in_stock') }}">
            @error('quantity_in_stock')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Warehouse Field --}}
        <div class="mb-4">
            <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Warehouse
            </label>
            <select name="supply_center_id" id="processedCoffeeWarehouse" required>
              <option value="">Select Warehouse</option>
              @foreach($supplyCenters as $center)
              <option value="{{ $center->id }}">{{ $center->name }}</option>
              @endforeach
            </select>
            @error('supply_center_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-modal> 

<!-- View Details Modal -->
<x-modal 
    id="viewDetailsModal" 
    title="Item Details" 
    size="xl" 
    submit-form=""
    submit-text=""
    cancel-text="Close">
    
    <div id="itemDetailsContent">
        <!-- Content will be loaded dynamically -->
    </div>
</x-modal>

@endsection

@push('scripts')
<script>
    
     if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
          const dataTable = new simpleDatatables.DataTable("#search-table", {
              searchable: true,
              sortable: false
          });
      }
      if (document.getElementById("search-table2") && typeof simpleDatatables.DataTable !== 'undefined') {
          const dataTable2 = new simpleDatatables.DataTable("#search-table2", {
              searchable: true,
              sortable: false
          });
      }

document.addEventListener('DOMContentLoaded', function () {

    // --- Essential Modal and Form Elements ---
    const rawCoffeeModal = document.getElementById('addRawCoffeeModal');
    const rawCoffeeForm = document.getElementById('addRawCoffeeForm');
    const processedCoffeeModal = document.getElementById('addProcessedCoffeeModal');
    const processedCoffeeForm = document.getElementById('addProcessedCoffeeForm');

    // Add Raw Coffee Button
    const addRawCoffeeButton = document.querySelector('button[data-modal-open="addRawCoffeeModal"]');
    if (addRawCoffeeButton && rawCoffeeModal && rawCoffeeForm) {
        addRawCoffeeButton.addEventListener('click', function() {
            rawCoffeeForm.reset();
            
            // Update modal title and button text
            const modalTitle = rawCoffeeModal.querySelector('h3');
            const submitButton = rawCoffeeModal.querySelector('button[type="submit"]');
            const methodInput = rawCoffeeForm.querySelector('input[name="_method"]');
            
            if (modalTitle) modalTitle.textContent = 'Add New Raw Coffee Item';
            if (submitButton) submitButton.textContent = 'Add Item';
            
            rawCoffeeForm.action = "{{ route('inventory.store') }}";
            if (methodInput) methodInput.value = 'POST';

            // Open modal
            rawCoffeeModal.classList.remove('hidden');
            rawCoffeeModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    }

    // Add Processed Coffee Button
    const addProcessedCoffeeButton = document.querySelector('button[data-modal-open="addProcessedCoffeeModal"]');
    if (addProcessedCoffeeButton && processedCoffeeModal && processedCoffeeForm) {
        addProcessedCoffeeButton.addEventListener('click', function() {
            processedCoffeeForm.reset();
            
            // Update modal title and button text
            const modalTitle = processedCoffeeModal.querySelector('h3');
            const submitButton = processedCoffeeModal.querySelector('button[type="submit"]');
            const methodInput = processedCoffeeForm.querySelector('input[name="_method"]');
            
            if (modalTitle) modalTitle.textContent = 'Add New Processed Coffee Item';
            if (submitButton) submitButton.textContent = 'Add Item';
            
            processedCoffeeForm.action = "{{ route('inventory.store') }}";
            if (methodInput) methodInput.value = 'POST';

            // Open modal
            processedCoffeeModal.classList.remove('hidden');
            processedCoffeeModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
    }

    // Edit Raw Coffee Buttons
    const editRawCoffeeButtons = document.querySelectorAll('.edit-RawCoffee-btn');
    editRawCoffeeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = btn.getAttribute('data-rawCoffee-id');
            const itemName = btn.getAttribute('data-rawCoffee-name');
            const itemGrade = btn.getAttribute('data-rawCoffee-grade');
            const itemQuantity = btn.getAttribute('data-rawCoffee-quantity');
            const itemLocation = btn.getAttribute('data-rawCoffee-location');

            // Validation
            if (!itemId || !itemName || !itemGrade || !itemQuantity || !itemLocation) {
                console.error("Missing raw coffee data:", { itemId, itemName, itemGrade, itemQuantity, itemLocation });
                alert("Error: Missing item data. Please refresh the page and try again.");
                return;
            }

            if (rawCoffeeForm && rawCoffeeModal) {
                rawCoffeeForm.reset();
                
                // Update modal elements
                const modalTitle = rawCoffeeModal.querySelector('h3');
                const submitButton = rawCoffeeModal.querySelector('button[type="submit"]');
                const methodInput = rawCoffeeForm.querySelector('input[name="_method"]');
                
                if (modalTitle) modalTitle.textContent = 'Edit Raw Coffee Item';
                if (submitButton) submitButton.textContent = 'Update Item';
                
                // Set form action and method
                const updateUrl = "{{ route('inventory.update.rawCoffee', ['rawCoffee' => ':id']) }}".replace(':id', encodeURIComponent(itemId));
                rawCoffeeForm.action = updateUrl;
                if (methodInput) methodInput.value = 'PATCH';

                // Populate form fields
                const nameSelect = document.getElementById('raw-coffee-name');
                const gradeInput = document.getElementById('rawCoffeeGrade');
                const quantityInput = document.getElementById('rawCoffeeQuantity');
                const warehouseSelect = document.getElementById('coffeeProductWarehouse');
                
                if (nameSelect) nameSelect.value = itemId;
                if (gradeInput) gradeInput.value = itemGrade;
                if (quantityInput) quantityInput.value = itemQuantity;
                if (warehouseSelect) {
                    // Find warehouse by name and set the value
                    for (let option of warehouseSelect.options) {
                        if (option.text === itemLocation) {
                            warehouseSelect.value = option.value;
                            break;
                        }
                    }
                }

                // Open modal
                rawCoffeeModal.classList.remove('hidden');
                rawCoffeeModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Edit Processed Coffee Buttons
    const editProcessedCoffeeButtons = document.querySelectorAll('.edit-CoffeeProduct-btn');
    editProcessedCoffeeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const itemId = btn.getAttribute('data-coffeeProduct-id');
            const itemName = btn.getAttribute('data-coffeeProduct-name');
            const itemCategory = btn.getAttribute('data-coffeeProduct-category');
            const itemQuantity = btn.getAttribute('data-coffeeProduct-quantity');
            const itemLocation = btn.getAttribute('data-coffeeProduct-location');

            // Validation
            if (!itemId || !itemName || !itemCategory || !itemQuantity || !itemLocation) {
                console.error("Missing processed coffee data:", { itemId, itemName, itemCategory, itemQuantity, itemLocation });
                alert("Error: Missing item data. Please refresh the page and try again.");
                return;
            }

            if (processedCoffeeForm && processedCoffeeModal) {
                processedCoffeeForm.reset();
                
                // Update modal elements
                const modalTitle = processedCoffeeModal.querySelector('h3');
                const submitButton = processedCoffeeModal.querySelector('button[type="submit"]');
                const methodInput = processedCoffeeForm.querySelector('input[name="_method"]');
                
                if (modalTitle) modalTitle.textContent = 'Edit Processed Coffee Item';
                if (submitButton) submitButton.textContent = 'Update Item';
                
                // Set form action and method
                const updateUrl = "{{ route('inventory.update.coffeeProduct', ['coffeeProduct' => ':id']) }}".replace(':id', encodeURIComponent(itemId));
                processedCoffeeForm.action = updateUrl;
                if (methodInput) methodInput.value = 'PATCH';

                // Populate form fields
                const nameSelect = document.getElementById('coffee-product-name');
                const categoryInput = document.getElementById('coffee-product-category');
                const quantityInput = document.getElementById('coffee-product-quantity');
                const warehouseSelect = document.getElementById('processedCoffeeWarehouse');
                
                if (nameSelect) nameSelect.value = itemId;
                if (categoryInput) categoryInput.value = itemCategory;
                if (quantityInput) quantityInput.value = itemQuantity;
                if (warehouseSelect) {
                    // Find warehouse by name and set the value
                    for (let option of warehouseSelect.options) {
                        if (option.text === itemLocation) {
                            warehouseSelect.value = option.value;
                            break;
                        }
                    }
                }

                // Open modal
                processedCoffeeModal.classList.remove('hidden');
                processedCoffeeModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Delete Raw Coffee Confirmation
    const deleteRawCoffeeForms = document.querySelectorAll('.delete-RawCoffee-form');
    deleteRawCoffeeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const itemName = form.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
            
            if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
                form.submit();
            }
        });
    });

    // Delete Processed Coffee Confirmation
    const deleteProcessedCoffeeForms = document.querySelectorAll('.delete-CoffeeProduct-form');
    deleteProcessedCoffeeForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const itemName = form.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
            
            if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
                form.submit();
            }
        });
    });

    // Modal close functionality
    function setupModalClose(modal) {
        if (!modal) return;
        
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            }
        });
        
        // Close on cancel button
        const cancelButton = modal.querySelector('button[data-modal-close]');
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = 'auto';
            });
        }
    }
    
    // Setup modal close for both modals
    setupModalClose(rawCoffeeModal);
    setupModalClose(processedCoffeeModal);
    
    // View Details functionality
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    const itemDetailsContent = document.getElementById('itemDetailsContent');
    
    viewDetailsButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const type = btn.getAttribute('data-type');
            const id = btn.getAttribute('data-id');
            
            // Show loading state
            itemDetailsContent.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading details...</p></div>';
            
            // Open modal
            viewDetailsModal.classList.remove('hidden');
            viewDetailsModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            
            // Fetch item details
            fetch(`/inventory/details/${type}/${id}`)
                .then(response => response.json())
                .then(data => {
                    let content = '';
                    
                    if (type === 'raw-coffee') {
                        content = `
                            <div class="space-y-6">
                                <!-- Total Quantity Section -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-xl font-semibold text-gray-900">${data.name}</h3>
                                    <div class="mt-2">
                                            <p class="text-gray-600">Total Quantity</p>
                                        <p class="text-2xl font-bold text-gray-900">${data.total_quantity.toFixed(2)} kg</p>
                                    </div>
                                </div>

                                <!-- Grade Breakdown -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Grade Breakdown</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        ${['A', 'B'].map(grade => {
                                            const gradeItems = data.inventory_details.filter(item => item.grade === grade);
                                            const gradeTotal = gradeItems.reduce((sum, item) => sum + parseFloat(item.quantity), 0);
                                            return `
                                                <div class="bg-white p-3 rounded-lg border">
                                                    <h5 class="font-medium text-gray-900">Grade ${grade}</h5>
                                                    <p class="text-xl font-bold text-gray-900">${gradeTotal.toFixed(2)} kg</p>
                                                </div>
                                            `;
                                        }).join('')}
                                    </div>
                                </div>
                                
                                <!-- Inventory Items List -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Inventory Items</h4>
                                    <div class="space-y-3">
                                        ${data.inventory_details.map(item => `
                                            <div class="bg-white p-4 rounded-lg border">
                                                <div class="flex justify-between items-start">
                                                    <div class="grid grid-cols-2 gap-4 flex-grow">
                                                        <div>
                                                            <p class="text-sm text-gray-500">Grade</p>
                                                            <p class="font-medium">Grade ${item.grade}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Quantity</p>
                                                            <p class="font-medium">${parseFloat(item.quantity).toFixed(2)} kg</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Warehouse</p>
                                                            <p class="font-medium">${item.supply_center}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Last Updated</p>
                                                            <p class="font-medium">${new Date(item.last_updated).toLocaleString()}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2 ml-4">
                                                        <button type="button" 
                                                            class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors duration-200"
                                                            onclick="editItem('raw-coffee', '${data.id}', '${item.grade}')">
                                                            Edit
                                                        </button>
                                                        <button type="button" 
                                                            class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors duration-200"
                                                            onclick="deleteItem('raw-coffee', '${data.id}', '${item.grade}')">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        content = `
                            <div class="space-y-6">
                                <!-- Total Quantity Section -->
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <h3 class="text-xl font-semibold text-gray-900">${data.name}</h3>
                                    <div class="mt-2">
                                            <p class="text-gray-600">Total Quantity</p>
                                        <p class="text-2xl font-bold text-gray-900">${data.total_quantity.toFixed(2)} kg</p>
                                    </div>
                                </div>

                                <!-- Category Breakdown -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Category Breakdown</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        ${['Premium', 'Standard'].map(category => {
                                            const categoryItems = data.inventory_details.filter(item => item.category === category);
                                            const categoryTotal = categoryItems.reduce((sum, item) => sum + parseFloat(item.quantity), 0);
                                            return `
                                                <div class="bg-white p-3 rounded-lg border">
                                                    <h5 class="font-medium text-gray-900">${category}</h5>
                                                    <p class="text-xl font-bold text-gray-900">${categoryTotal.toFixed(2)} kg</p>
                                                </div>
                                            `;
                                        }).join('')}
                                    </div>
                                </div>
                                
                                <!-- Inventory Items List -->
                                <div>
                                    <h4 class="text-lg font-semibold text-gray-900 mb-3">Inventory Items</h4>
                                    <div class="space-y-3">
                                        ${data.inventory_details.map(item => `
                                            <div class="bg-white p-4 rounded-lg border">
                                                <div class="flex justify-between items-start">
                                                    <div class="grid grid-cols-2 gap-4 flex-grow">
                                                        <div>
                                                            <p class="text-sm text-gray-500">Category</p>
                                                            <p class="font-medium">${item.category}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Quantity</p>
                                                            <p class="font-medium">${parseFloat(item.quantity).toFixed(2)} kg</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Warehouse</p>
                                                            <p class="font-medium">${item.supply_center}</p>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm text-gray-500">Last Updated</p>
                                                            <p class="font-medium">${new Date(item.last_updated).toLocaleString()}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex space-x-2 ml-4">
                                                        <button type="button" 
                                                            class="px-3 py-1 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors duration-200"
                                                            onclick="editItem('coffee-product', '${data.id}', '${item.category}')">
                                                            Edit
                                                        </button>
                                                        <button type="button" 
                                                            class="px-3 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors duration-200"
                                                            onclick="deleteItem('coffee-product', '${data.id}', '${item.category}')">
                                                            Delete
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                    
                    itemDetailsContent.innerHTML = content;
                })
                .catch(error => {
                    console.error('Error fetching item details:', error);
                    itemDetailsContent.innerHTML = '<div class="text-center py-8"><p class="text-red-600">Error loading details. Please try again.</p></div>';
                });
        });
    });
    
    // Setup modal close for view details modal
    setupModalClose(viewDetailsModal);
});

// Global functions for edit and delete
function editItem(type, id, gradeOrCategory) {
    // Close the details modal
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    viewDetailsModal.classList.add('hidden');
    viewDetailsModal.classList.remove('flex');
    document.body.style.overflow = 'auto';
    
    // Open the appropriate edit modal based on type
    if (type === 'raw-coffee') {
        const rawCoffeeModal = document.getElementById('addRawCoffeeModal');
        const rawCoffeeForm = document.getElementById('addRawCoffeeForm');
        
        if (rawCoffeeModal && rawCoffeeForm) {
            // Update modal title and button text
            const modalTitle = rawCoffeeModal.querySelector('h3');
            const submitButton = rawCoffeeModal.querySelector('button[type="submit"]');
            const methodInput = rawCoffeeForm.querySelector('input[name="_method"]');
            const gradeInput = document.getElementById('rawCoffeeGrade');
            
            if (modalTitle) modalTitle.textContent = 'Edit Raw Coffee Item';
            if (submitButton) submitButton.textContent = 'Update Item';
            if (gradeInput) gradeInput.value = gradeOrCategory;
            
            // Set form action and method
            const updateUrl = `/inventory/raw-coffee/${id}`;
            rawCoffeeForm.action = updateUrl;
            if (methodInput) methodInput.value = 'PATCH';
            
            // Open modal
            rawCoffeeModal.classList.remove('hidden');
            rawCoffeeModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    } else {
        const processedCoffeeModal = document.getElementById('addProcessedCoffeeModal');
        const processedCoffeeForm = document.getElementById('addProcessedCoffeeForm');
        
        if (processedCoffeeModal && processedCoffeeForm) {
            // Update modal title and button text
            const modalTitle = processedCoffeeModal.querySelector('h3');
            const submitButton = processedCoffeeModal.querySelector('button[type="submit"]');
            const methodInput = processedCoffeeForm.querySelector('input[name="_method"]');
            const categoryInput = document.getElementById('coffee-product-category');
            
            if (modalTitle) modalTitle.textContent = 'Edit Processed Coffee Item';
            if (submitButton) submitButton.textContent = 'Update Item';
            if (categoryInput) categoryInput.value = gradeOrCategory;
            
            // Set form action and method
            const updateUrl = `/inventory/coffee-product/${id}`;
            processedCoffeeForm.action = updateUrl;
            if (methodInput) methodInput.value = 'PATCH';
            
            // Open modal
            processedCoffeeModal.classList.remove('hidden');
            processedCoffeeModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }
    }
}

function deleteItem(type, id, gradeOrCategory) {
    const itemType = type === 'raw-coffee' ? 'raw coffee' : 'processed coffee';
    const itemIdentifier = type === 'raw-coffee' ? `Grade ${gradeOrCategory}` : gradeOrCategory;
    
    if (confirm(`Are you sure you want to delete this ${itemType} item (${itemIdentifier})? This action cannot be undone.`)) {
        let url;
        if (type === 'raw-coffee') {
            url = `/inventory/raw-coffee/${id}`;
        } else {
            url = `/inventory/coffee-product/${id}`;
        }
        
        // Create a form to submit the delete request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        // Add method override
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        // Add grade/category parameter
        const gradeOrCategoryInput = document.createElement('input');
        gradeOrCategoryInput.type = 'hidden';
        gradeOrCategoryInput.name = type === 'raw-coffee' ? 'grade' : 'category';
        gradeOrCategoryInput.value = gradeOrCategory;
        form.appendChild(gradeOrCategoryInput);
        
        // Submit the form
        document.body.appendChild(form);
        form.submit();
    }
}
  function updateStatsCards() {
    fetch('/inventory/stats')
        .then(response => response.json())
        .then(data => {
            // Update the card values by their IDs
            const arabicaElement = document.getElementById('raw-coffee-arabica-quantity');
            const robustaElement = document.getElementById('raw-coffee-robusta-quantity');
            const mountainBrewElement = document.getElementById('processed-coffee-mountain-brew-quantity');
            const morningBrewElement = document.getElementById('processed-coffee-morning-brew-quantity');
            
            if (arabicaElement) arabicaElement.textContent = data.rawCoffeeArabicaQuantity || '0';
            if (robustaElement) robustaElement.textContent = data.rawCoffeeRobustaQuantity || '0';
            if (mountainBrewElement) mountainBrewElement.textContent = data.processedCoffeeMountainBrewQuantity || '0';
            if (morningBrewElement) morningBrewElement.textContent = data.processedCoffeeMorningBrewQuantity || '0';
            
            // Update percentage changes
            updatePercentageChange('arabica', data.arabicaChange);
            updatePercentageChange('robusta', data.robustaChange);
            updatePercentageChange('mountain-brew', data.mountainBrewChange);
            updatePercentageChange('morning-brew', data.morningBrewChange);
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
        });
  }
  
  function updatePercentageChange(type, change) {
    const card = document.querySelector(`[data-change-type="${type}"]`);
    if (card) {
      const arrowElement = card.querySelector('.fa-arrow-up, .fa-arrow-down');
      const textElement = card.querySelector('.text-xs.font-medium');
      
      if (arrowElement && textElement) {
        // Update arrow direction
        arrowElement.className = `fa-solid ${change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'} text-xs ${change >= 0 ? 'text-green-500' : 'text-red-500'}`;
        
        // Update text
        textElement.textContent = `${change}% from last week`;
        textElement.className = `text-xs font-medium ml-1 ${change >= 0 ? 'text-green-500' : 'text-red-500'}`;
      }
    }
  }
  // Call once on page load
   updateStatsCards();
   // Refresh every 60 seconds
   setInterval(updateStatsCards, 60000);

</script>
@endpush