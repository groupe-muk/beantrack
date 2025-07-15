@extends('layouts.main-view')

@section('content')
<div class="p-5 bg-light-background">
  <!-- Header Section -->
  <h1 class="text-3xl font-bold text-dashboard-light">Supplier Coffee Inventory Management</h1>
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
  <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4 mb-8">
    <x-stats-card
      title="Arabica"
      :value="number_format($arabicaQuantity, 2)"
      unit="kg"
      iconClass="fa-cube"
    />
    <x-stats-card
      title="Robusta"
      :value="number_format($robustaQuantity, 2)"
      unit="kg"
      iconClass="fa-cube"
    />
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
      <button class="bg-light-brown text-white px-4 py-2 rounded" data-modal-open="addRawCoffeeModal">+ Add Item</button>
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
                {{ sprintf('RC%05d', $item->id) }}
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
                  data-type="{{ $item->name }}"
                  onclick="viewDetails('{{ $item->name }}')">
                  
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

<!-- View Details Modal -->
<x-modal id="viewDetailsModal"
 title="Raw Coffee Details" 
 size="xl"
 submit-form=""
  cancel-text="Close"
  >
  <div class=" overflow-visible pr-0" id="itemDetailsContent">
    <!-- Content will be loaded dynamically -->
    </div>

  
  
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

    document.querySelector('[data-modal-open="addRawCoffeeModal"]').addEventListener('click', () => {
    // Reset the form
    document.getElementById('addRawCoffeeForm').reset();
    
    // Reset form method to POST
    document.getElementById('form-method').value = '';
    
    // Reset form action
    document.getElementById('addRawCoffeeForm').action = "{{ route('supplierInventory.store') }}";
    
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
});

    function viewDetails(coffeeType) {
        // Show loading state
        const detailsContent = document.getElementById('itemDetailsContent');
        if (detailsContent) {
            detailsContent.innerHTML = '<div class="flex justify-center items-center py-10"><i class="fas fa-spinner fa-spin text-3xl text-light-brown"></i></div>';
        }
        
        // Open modal
        const detailsModal = document.getElementById('viewDetailsModal');
        detailsModal.classList.remove('hidden');
        detailsModal.classList.add('flex');
        document.body.style.overflow = 'hidden';
        
        // Set modal title
        const modalTitle = detailsModal.querySelector('.modal-title');
        if (modalTitle) {
            modalTitle.textContent = coffeeType + ' Details';
        }
        
        // Fetch details from the server
        fetch(`/supplierInventory/details/${coffeeType}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Populate the modal with the inventory details
                populateDetailsModal(data, coffeeType);
            })
            .catch(error => {
                console.error('Error fetching details:', error);
                detailsContent.innerHTML = `<div class="text-red-500 text-center py-5">
                    Error loading details. Please try again.
                </div>`;
            });
    }
    
    function populateDetailsModal(data, coffeeType) {
        const detailsContent = document.getElementById('itemDetailsContent');
        if (!detailsContent) return;
        
        // Calculate total quantity
        let totalQuantity = 0;
        Object.values(data.gradeQuantities).forEach(qty => totalQuantity += parseFloat(qty));
        
        let html = `
            <div class="mb-4">
                <h3 class="text-lg font-semibold mb-2">${coffeeType} - Total: ${totalQuantity.toFixed(2)} kg</h3>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Grade A</p>
                        <p class="font-semibold">${parseFloat(data.gradeQuantities.A).toFixed(2)} kg</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded">
                        <p class="text-sm text-gray-600">Grade B</p>
                        <p class="font-semibold">${parseFloat(data.gradeQuantities.B).toFixed(2)} kg</p>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Warehouse
                            </th>
                            <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Grade
                            </th>
                            <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Quantity
                            </th>
                            <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Last Updated
                            </th>
                            <th class="px-5 py-3 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                Actions
                                </th>
                        </tr>
                    </thead>
                    <tbody>`;
        
        if (data.inventoryItems && data.inventoryItems.length > 0) {
            data.inventoryItems.forEach(item => {
                const date = new Date(item.updated_at);
                const formattedDate = date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                html += `
                    <tr>
                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                            ${item.warehouse}
                        </td>
                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                            ${item.grade}
                        </td>
                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                            ${parseFloat(item.quantity).toFixed(2)} kg
                        </td>
                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm">
                            ${formattedDate}
                        </td>
                        <td class="px-5 py-3 border-b border-gray-200 bg-white text-sm"
                                        <div class=" space-x-2">
                            <div class="flex space-x-2">
                                <button 
                                    type="button"
                                    onclick="editInventoryItem('${item.id}')" 
                                    class="text-blue-600 hover:text-blue-900 text-sm">
                                    Edit
                                </button>
                                <button 
                                    type="button"
                                    onclick="confirmDeleteItem('${item.id}')"
                                    class="text-red-600 hover:text-red-900 text-sm">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>`;
            });
        } else {
            html += `
                <tr>
                    <td colspan="4" class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                        No inventory details available.
                    </td>
                </tr>`;
        }
        
        html += `
                    </tbody>
                </table>
            </div>`;
        
        detailsContent.innerHTML = html;
    }

    function editInventoryItem(itemId) {
        // Show loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
        loadingOverlay.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><i class="fas fa-spinner fa-spin text-light-brown mr-2"></i> Loading...</div>';
        document.body.appendChild(loadingOverlay);
        
        // Close the details modal
        const detailsModal = document.getElementById('viewDetailsModal');
        detailsModal.classList.add('hidden');
        detailsModal.classList.remove('flex');
        
        // Fetch the inventory item data
        fetch(`/supplierInventory/item/${itemId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
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
                methodInput.value = 'PUT';
                
                // Set form action to update endpoint
                form.action = `/supplierInventory/item/${itemId}`;
                
                // Populate form fields
                if (data.raw_coffee_id) {
                    const rawCoffeeSelect = document.getElementById('raw-coffee-name');
                    rawCoffeeSelect.value = data.raw_coffee_id;
                }
                
                const gradeInput = document.getElementById('rawCoffeeGrade');
                gradeInput.value = data.grade || '';
                
                const quantityInput = document.getElementById('rawCoffeeQuantity');
                quantityInput.value = data.quantity_in_stock || '';
                
                if (data.supply_center_id) {
                    const warehouseSelect = document.getElementById('coffeeProductWarehouse');
                    warehouseSelect.value = data.supply_center_id;
                }
                
                // Show the edit modal
                editModal.classList.remove('hidden');
                editModal.classList.add('flex');
                document.body.style.overflow = 'hidden';
            })
            .catch(error => {
                // Remove loading overlay
                document.body.removeChild(loadingOverlay);
                
                console.error('Error fetching item data:', error);
                alert('Error loading item data. Please try again.');
            });
    }

    function confirmDeleteItem(itemId) {
        if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
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

// General modal control functions
document.addEventListener('DOMContentLoaded', function() {
    // Setup modal triggers
    document.querySelectorAll('[data-modal-open]').forEach(button => {
        const modalId = button.getAttribute('data-modal-open');
        const modal = document.getElementById(modalId);
        
        button.addEventListener('click', () => {
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
});
</script>
@endpush