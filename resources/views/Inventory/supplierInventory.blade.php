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
    title="Total Value"
    value="13,907.56"
    unit="Ugx"
    changeText="2 from last week"
    iconClass="fa-cube"
    />
    </div>
  </div>
   <!-- Inventory Table -->
  <div class="mt-10">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-semibold text-dashboard-light pb-5">Raw Coffee</h1>
      <div>
        <button class="bg-light-brown text-white px-4 py-2 rounded" data-mode="add" data-modal-open="addRawCoffeeModal">+ Add Item</button>
      </div>
  </div>
  
    <div class="bg-white rounded shadow overflow-x-auto p-4">
      <table class="min-w-full leading-normal" id="search-table">
        <thead>
          <tr class="bg-gray-100 text-left">
            <th class="px-4 py-2">SKU</th>
            <th class="px-4 py-2">Product Name</th>
            <th class="px-4 py-2">Grade</th>
            <th class="px-4 py-2">Quantity</th>
            <th class="px-4 py-2">Warehouse</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody>
        @forelse ($rawCoffeeInventory as $rawCoffee)
                        <tr class="hover:bg-gray-50 dark:hover:bg-mild-gray transition-colors duration-150">
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $rawCoffee->rawCoffee->id }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $rawCoffee->rawCoffee->coffee_type }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $rawCoffee->rawCoffee->grade }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $rawCoffee->quantity_in_stock }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                                {{ $rawCoffee->supplyCenter->name }}
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm text-gray-900 dark:text-off-white">
                            </td>
                            <td class="px-5 py-5 border-b border-soft-gray dark:border-mild-gray text-sm">
                                <div class="flex items-center space-x-3">
                                    {{-- Edit button --}}
                                    <button 
                                        type="button"
                                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 text-xs cursor-pointer edit-RawCoffee-btn"
                                        data-rawCoffee-id="{{ $rawCoffee->rawCoffee->id }}"
                                        data-rawCoffee-name="{{ $rawCoffee->rawCoffee->coffee_type }}"
                                        data-rawCoffee-grade="{{ $rawCoffee->rawCoffee->grade }}"
                                        data-rawCoffee-quantity="{{ $rawCoffee->quantity_in_stock }}"
                                        data-rawCoffee-location="{{ $rawCoffee->supplyCenter->name }}"
                                        data-mode="edit">
                                        Edit
                                    </button>
                                    {{-- Delete button --}}
                                    <form action="{{ route('inventory.destroy', $rawCoffee->rawCoffee->id) }}" method="POST" class="inline delete-RawCoffee-form">
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
                                No items found.
                            </td>
                        </tr>
                    @endforelse
        </tbody>
      </table>
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
            @error('raw_coffee_name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Grade Field --}}
        <div class="mb-4">
            <label for="grade" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
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
            <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Quantity
            </label>
            <input type="number" 
                   id="rawCoffeeQuantity" 
                   name="quantity_in_stock" 
                   required
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                   placeholder="Enter Quantity"
                   value="{{ old('quantity') }}">
            @error('quantity')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Warehouse Field --}}
        <div class="mb-4">
            <label for="warehouse" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Warehouse
            </label>
            <select name="supply_center_id" id="coffeeProductWarehouse">
              <option value="">Select Warehouse</option>
              @foreach($supplyCenters as $center)
              <option value="{{ $center->id }}">{{ $center->name }}</option>
              @endforeach
            </select>
            @error('supply-center-id')
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