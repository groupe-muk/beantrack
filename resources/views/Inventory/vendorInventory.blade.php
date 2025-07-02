@extends('layouts.main-view')

@section('content')
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
    title="Out Of Stock"
    value="6"
    changeText="2 from last week"
    iconClass="fa-exclamation-triangle"
    />
    <x-stats-card
    title="Low Stock Alerts"
    value="6"
    changeText="2 from last week"
    iconClass="fa-long-arrow-down"
    />
    <x-stats-card
    title="Total items"
    value="10"
    changeText="2 from last week"
    iconClass="fa-cube"
    />
    </div>
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
            <th class="px-4 py-2">Category</th>
            <th class="px-4 py-2">Quantity</th>
            <th class="px-4 py-2">Warehouse</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
           @forelse ($coffeeProductInventory as $coffeeProduct)
                        <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors duration-150">
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $coffeeProduct->coffeeProduct->id }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $coffeeProduct->coffeeProduct->name }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $coffeeProduct->coffeeProduct->category }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $coffeeProduct->quantity_in_stock }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $coffeeProduct->supplyCenter->name }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm">
                                <div class="flex items-center space-x-3">
                                    {{-- Edit button --}}
                                    <button 
                                        type="button"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 text-xs cursor-pointer edit-CoffeeProduct-btn"
                                        data-coffeeProduct-id="{{ $coffeeProduct->CoffeeProduct->id }}"
                                        data-coffeeProduct-name="{{ $coffeeProduct->CoffeeProduct->name }}"
                                        data-coffeeProduct-category="{{ $coffeeProduct->category }}"
                                        data-coffeeProduct-quantity="{{ $coffeeProduct->quantity_in_stock }}"
                                        data-coffeeProduct-location="{{ $coffeeProduct->SupplyCenter->name }}"
                                        data-mode="edit">
                                        Edit
                                    </button>
                                    {{-- Delete button --}}
                                    <form action="{{ route('inventory.destroy', $coffeeProduct->id) }}" method="POST" class="inline delete-CoffeeProduct-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600 transition-colors duration-200 text-xs cursor-pointer">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-center text-sm text-gray-500 dark:text-gray-400">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
        </tbody>
      </table>
    </div>
  </div>
  <x-modal 
    id="addCoffeeProductModal" 
    title="Add New Coffee Product Item" 
    size="md" 
    submit-form="addCoffeeProductForm" 
    submit-text="Add Item"
    cancel-text="Cancel">
    
    <form action="{{ route('vendorInventory.store') }}" method="POST" id="addCoffeeProductForm">
        @csrf
        <input type="hidden" name="_method" id="form-method" value="">

        {{-- Name Field --}}
        <div class="mb-4">
            <label for="coffee_product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                 Coffee Product Name
            </label>
            <select name="coffee_product_id" id="coffee-product-name" required>
              <option value="">Select Coffee Product Item</option>
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
            <label for="quantity_in_stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Quantity
            </label>
            <input type="number" 
                   id="coffee-product-quantity" 
                   name="quantity_in_stock" 
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
            <select name="supply_center_id" id="processedCoffeeWarehouse">
              <option value="">Select Warehouse</option>
              @foreach($supplyCenters as $center)
              <option value="{{ $center->id }}">{{ $center->name }}</option>
              @endforeach
            </select>
            @error('warehouse')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </form>
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
      
       </script>
@endpush


  