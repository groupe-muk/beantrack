@extends('layouts.main-view')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="p-5 bg-light-background">
<!-- Header Section -->
  
  <h1 class="text-3xl font-bold text-dashboard-light"> Vendor coffee products inventory management</h1>

  <p class="text-soft-brown mb-4">Manage your coffee products inventory and monitor stock levels</p>

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
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 w-full">
      <x-stats-card
        title="Mountain Blend"
        :value="$mountainBlendQuantity"
        valueId="mountain-blend-quantity"
        unit="kg"
        iconClass="fa-cube"
      />
      
      <x-stats-card
        title="Morning Brew"
        :value="$morningBrewQuantity"
        valueId="morning-brew-quantity"
        unit="kg"
        iconClass="fa-cube"
      />
      
      <x-stats-card
        title="Total Quantity"
        :value="$totalQuantity"
        valueId="total-quantity"
        unit="kg"
        iconClass="fa-cube"
      />
    </div>
    
    <!-- Inventory Table -->
  
        
   <div class="flex justify-between items-center mb-6 pt-10">
    <h1 class="text-2xl font-semibold text-dashboard-light pb-5">Coffee Product</h1>
      <div>
        <button class="bg-light-brown text-white px-4 py-2 rounded" data-mode="add" data-modal-open="addCoffeeProductModal">+ Add Item</button>
      </div>
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
                $uniqueProducts = $inventoryItems->groupBy('coffee_product_id')->map(function($group) {
                    return [
                        'id' => $group->first()->coffeeProduct->id,
                        'name' => $group->first()->coffeeProduct->name,
                        'total_quantity' => $group->sum('quantity_in_stock'),
                        'categories' => $group->pluck('coffeeProduct.category')->unique()
                    ];
                });
            @endphp
            @forelse ($uniqueProducts as $product)
                <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors duration-150">
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        {{ $product['id'] }}
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        {{ $product['name'] }}
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        {{ $product['total_quantity'] }}
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                        @if ($product['total_quantity'] > 10)
                            <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs">In Stock</span>
                        @elseif ($product['total_quantity'] > 0)
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs">Low Stock</span>
                        @else
                            <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">Out of Stock</span>
                        @endif
                    </td>
                    <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm">
                        <button type="button"
                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 text-xs cursor-pointer view-details-btn"
                            data-product-id="{{ $product['id'] }}">
                            View Details
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-center text-sm text-gray-500 dark:text-gray-400">
                        No products found.
                    </td>
                </tr>
            @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <!-- Add Coffee Product Modal -->
<x-modal 
    id="addCoffeeProductModal" 
    title="Add New Coffee Product Item" 
    size="md" 
    submit-form="addCoffeeProductForm" 
    submit-text="Add Item"
    cancel-text="Cancel">
    
    <form action="{{ route('vendorInventory.store') }}" method="POST" id="addCoffeeProductForm">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="POST">

        {{-- Name Field --}}
        <div class="mb-4">
            <label for="coffee_product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Coffee Product Name
            </label>
            <select name="coffee_product_id" id="coffee-product-name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select Coffee Product Item</option>
                @foreach($coffeeProductItems as $coffeeProductItem)
                    <option value="{{ $coffeeProductItem->id }}" data-category="{{ $coffeeProductItem->category }}">{{ $coffeeProductItem->name }}</option>
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
            <select 
                   id="coffee-product-category" 
                   name="category" 
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select Category</option>
                <option value="premium" {{ old('category') == 'premium' ? 'selected' : '' }}>Premium</option>
                <option value="standard" {{ old('category') == 'standard' ? 'selected' : '' }}>Standard</option>
            </select>
            @error('category')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Quantity Field --}}
        <div class="mb-4">
            <label for="quantity_in_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Quantity
            </label>
            <input type="number" 
                   id="coffee-product-quantity" 
                   name="quantity_in_stock" 
                   required
                   min="0"
                   step="0.01"
                   style="-moz-appearance: textfield;"
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                   placeholder="0.00"
                   value="{{ old('quantity_in_stock') }}">
            @error('quantity_in_stock')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Warehouse Field --}}
        <div class="mb-4">
            <label for="supply_center_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Warehouse
            </label>
            <select name="supply_center_id" id="processedCoffeeWarehouse" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
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

<!-- Product Details Modal -->
<div id="productDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50" style="backdrop-filter: blur(4px);">
    <div class="bg-white rounded-lg w-full max-w-3xl mx-4 overflow-y-auto max-h-[90vh]">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-lg font-semibold">Product Details</h3>
            <button type="button" onclick="closeProductDetails()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div id="productDetailsContent" class="p-4 space-y-4">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>
  @endsection
  @push('scripts')
  <script>


      if (document.getElementById("search-table") && typeof simpleDatatables.DataTable !== 'undefined') {
          const dataTable = new simpleDatatables.DataTable("#search-table", {
              searchable: true,
              sortable: false
          });
      }


      function updateStatsCards() {
    fetch('/vendorInventory/stats')
        .then(response => response.json())
        .then(data => {
            // Update Mountain Blend card
            document.getElementById('mountain-blend-quantity').textContent = data.mountainBlendQuantity;
            updateTrendIndicator('mountain-blend-quantity', data.mountainBlendChange);

            // Update Morning Brew card
            document.getElementById('morning-brew-quantity').textContent = data.morningBrewQuantity;
            updateTrendIndicator('morning-brew-quantity', data.morningBrewChange);

            // Update Total Quantity card
            document.getElementById('total-quantity').textContent = data.totalQuantity;
            updateTrendIndicator('total-quantity', data.totalChange);
        })
        .catch(error => {
            console.error('Error fetching stats:', error);
        });
  }

  function updateTrendIndicator(elementId, change) {
    const element = document.getElementById(elementId);
    if (!element) return;

    const card = element.closest('.stats-card');
    if (!card) return;

    const trendIcon = card.querySelector('.trend-icon');
    const trendText = card.querySelector('.trend-text');

    if (trendIcon) {
        trendIcon.className = `trend-icon fas ${change >= 0 ? 'fa-arrow-up text-green-500' : 'fa-arrow-down text-red-500'}`;
    }
    if (trendText) {
        trendText.textContent = `${change}% from last week`;
        trendText.className = `trend-text ${change >= 0 ? 'text-green-500' : 'text-red-500'}`;
    }
}

// Call once on page load
updateStatsCards();
// Refresh every 60 seconds
setInterval(updateStatsCards, 60000);

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
                const updateUrl = "{{ route('vendorInventory.update', ['coffeeProduct' => ':id']) }}".replace(':id', encodeURIComponent(itemId));
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

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded'); // Debug log

    // Add click event listeners to all view details buttons
    const viewDetailsButtons = document.querySelectorAll('.view-details-btn');
    console.log('Found view details buttons:', viewDetailsButtons.length); // Debug log
    
    viewDetailsButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const productId = this.getAttribute('data-product-id');
            console.log('Button clicked for product:', productId); // Debug log
            showProductDetails(productId);
        });
    });
});

function showProductDetails(productId) {
    console.log('Fetching details for product:', productId);
    
    // Show loading state
    const content = document.getElementById('productDetailsContent');
    content.innerHTML = '<div class="flex justify-center items-center p-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div></div>';
    
    // Show modal immediately with loading state
    const modal = document.getElementById('productDetailsModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');

    fetch(`/vendorInventory/details/${productId}`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Received data:', data);
        
        if (data.error) {
            throw new Error(data.error);
        }

        const detailsHtml = `
            <div class="bg-white rounded-lg">
                <div class="mb-6">
                    <h3 class="text-xl font-semibold mb-2">${data.name}</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-600">SKU:</span>
                            <span class="font-medium">${data.id}</span>
                        </div>
                        <div>
                            <span class="text-gray-600">Total Quantity:</span>
                            <span class="font-medium">${data.total_quantity} kg</span>
                        </div>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-lg font-semibold mb-2">Category Totals</h4>
                    <div class="grid grid-cols-2 gap-4">
                        ${Object.entries(data.category_totals).map(([category, total]) => `
                            <div class="bg-gray-50 p-3 rounded">
                                <span class="font-medium capitalize">${category}:</span>
                                <span>${total} kg</span>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <div class="mt-6">
                    <div class="mb-4">
                        <h4 class="text-lg font-semibold">Inventory Items</h4>
                    </div>
                    ${data.inventory_items.length === 0 ? `
                        <p class="text-gray-500 text-center py-4">No inventory items found.</p>
                    ` : `
                        <div class="space-y-4">
                            ${data.inventory_items.map(item => `
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between items-center mb-3">
                                        <h5 class="font-medium text-lg capitalize">${item.category}</h5>
                                        <div class="space-x-2">
                                            <button type="button" 
                                                onclick="editInventory('${data.id}', '${item.id}')" 
                                                class="text-blue-600 hover:text-blue-900 text-sm">
                                                Edit
                                            </button>
                                            <button type="button" 
                                                onclick="deleteInventory('${item.id}')"
                                                class="text-red-600 hover:text-red-900 text-sm">
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-600">Quantity:</span>
                                            <span class="font-medium">${item.quantity} kg</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Warehouse:</span>
                                            <span class="font-medium">${item.warehouse}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Added on:</span>
                                            <span class="font-medium">${item.created_at}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-600">Last Updated:</span>
                                            <span class="font-medium">${item.last_updated}</span>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `}
                </div>
            </div>
        `;
        
        content.innerHTML = detailsHtml;
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = `
            <div class="text-center text-red-600 p-4">
                <p>Error loading product details.</p>
                <p class="text-sm">${error.message}</p>
            </div>
        `;
    });
}

function closeProductDetails() {
    console.log('Closing modal'); // Debug log
    const modal = document.getElementById('productDetailsModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function addInventory(productId) {
    // Set the form for adding new inventory
    const form = document.getElementById('addCoffeeProductForm');
    const nameSelect = document.getElementById('coffee-product-name');
    
    // Set the product ID
    nameSelect.value = productId;
    
    // Show the modal
    const modal = document.getElementById('addCoffeeProductModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Close the details modal
    closeProductDetails();
}

function editInventory(productId, inventoryId) {
    fetch(`/vendorInventory/${inventoryId}/edit`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const form = document.getElementById('addCoffeeProductForm');
        const nameSelect = document.getElementById('coffee-product-name');
        const categoryInput = document.getElementById('coffee-product-category');
        const quantityInput = document.getElementById('coffee-product-quantity');
        const warehouseSelect = document.getElementById('processedCoffeeWarehouse');
        
        // Update form action and method
        form.action = `/vendorInventory/${inventoryId}`;
        const methodInput = document.getElementById('form-method');
        methodInput.value = 'PATCH';
        
        // Set the values
        nameSelect.value = productId;
        categoryInput.value = data.category;
        quantityInput.value = data.quantity_in_stock;
        warehouseSelect.value = data.supply_center_id;
        
        // Show the modal
        const modal = document.getElementById('addCoffeeProductModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Close the details modal
        closeProductDetails();
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load inventory details for editing');
    });
}

function deleteInventory(inventoryId) {
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        fetch(`/vendorInventory/${inventoryId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to delete inventory');
            }
            // Close the details modal
            closeProductDetails();
            // Refresh the page to show updated data
            window.location.reload();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to delete inventory item. Please try again.');
        });
    }
}

// Close modal when clicking outside
document.getElementById('productDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductDetails();
    }
});

      
       </script>
@endpush


  