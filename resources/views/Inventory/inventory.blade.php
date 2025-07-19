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
        <div class="flex space-x-2">
            <button class="bg-light-brown text-white px-4 py-2 rounded hover:bg-coffee-brown transition-colors" data-modal-open="addRawCoffeeModal">+ Add to Inventory</button>
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
                            {{ $loop->iteration }}
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
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 cursor-pointer view-details-btn"
                                    data-type="raw-coffee"
                                    data-id="{{ $rawCoffee->id }}"
                                    data-coffee-type="{{ $rawCoffee->coffee_type }}"
                                    data-current-grade="{{ $rawCoffee->grade ?? 'Unknown' }}">
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
    <div class="flex space-x-2">
        <button class="bg-coffee-brown text-white px-4 py-2 rounded hover:bg-light-brown transition-colors" data-modal-open="createCoffeeProductModal">+ Create New Product</button>
        <button class="bg-light-brown text-white px-4 py-2 rounded hover:bg-coffee-brown transition-colors" data-mode="add" data-modal-open="addProcessedCoffeeModal">+ Add to Inventory</button>
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
                        {{ $loop->iteration }}
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
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600 transition-colors duration-200 cursor-pointer view-details-btn"
                                data-type="coffee-product"
                                data-id="{{ $coffeeProduct->id }}"
                                data-product-name="{{ $coffeeProduct->name ?? 'Unknown' }}"
                                data-current-category="{{ $coffeeProduct->category ?? 'Unknown' }}">
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
                Grade (Auto-selected)
            </label>
            <input type="text" 
                id="rawCoffeeGrade" 
                name="grade" 
                readonly
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400"
                placeholder="Grade will be auto-selected"
                value="{{ old('grade') }}">
            @error('grade')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Grade is automatically set based on selected coffee item.
            </p>
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
              <option value="{{ $coffeeProductItem->id }}" data-category="{{ $coffeeProductItem->category }}">
                {{ $coffeeProductItem->name }} - {{ ucfirst($coffeeProductItem->category) }}
              </option>
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
                   readonly
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md 
                          bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400 
                          cursor-not-allowed"
                   placeholder="Select coffee product to see category"
                   value="{{ old('category') }}">
            @error('category')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Category is automatically set based on selected coffee product.
            </p>
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

<!-- Create New Coffee Product Modal -->
<x-modal 
    id="createCoffeeProductModal" 
    title="Create New Coffee Product" 
    size="md" 
    submit-form="createCoffeeProductForm" 
    submit-text="Create Product"
    cancel-text="Cancel">
    
    <form action="{{ route('coffeeproduct.store') }}" method="POST" id="createCoffeeProductForm">
        @csrf
        
        {{-- Raw Coffee Source Field --}}
        <div class="mb-4">
            <label for="raw_coffee_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Raw Coffee Source *
            </label>
            <select name="raw_coffee_id" id="raw_coffee_id" required>
                <option value="">Select Raw Coffee Source</option>
                @foreach($rawCoffeeItems as $rawCoffeeItem)
                    <option value="{{ $rawCoffeeItem->id }}">{{ $rawCoffeeItem->coffee_type }} - {{ $rawCoffeeItem->grade }}</option>
                @endforeach
            </select>
            @error('raw_coffee_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
        
        {{-- Product Name Field --}}
        <div class="mb-4">
            <label for="product_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Product Name *
            </label>
            <input type="text" 
                id="product_name" 
                name="name" 
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white"
                placeholder="e.g., Premium Espresso Blend, Single Origin Pour Over"
                value="{{ old('name') }}">
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Category Field --}}
        <div class="mb-4">
            <label for="categories" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Categories * (Select multiple)
            </label>
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" name="categories[]" value="premium" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('premium', old('categories', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Premium</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="categories[]" value="standard" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('standard', old('categories', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Standard</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="categories[]" value="specialty" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('specialty', old('categories', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Specialty</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" name="categories[]" value="organic" 
                           class="rounded border-gray-300 text-coffee-brown focus:ring-coffee-brown"
                           {{ in_array('organic', old('categories', [])) ? 'checked' : '' }}>
                    <span class="ml-2">Organic</span>
                </label>
            </div>
            @error('categories')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                Select all categories that will be available for this product. Each category can have separate inventory.
            </p>
        </div>

        {{-- Product Form Field --}}
        <div class="mb-4">
            <label for="product_form" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Product Form *
            </label>
            <select name="product_form" 
                id="product_form"
                required
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select product form</option>
                <option value="Whole Bean" {{ old('product_form') == 'Whole Bean' ? 'selected' : '' }}>Whole Bean</option>
                <option value="Ground" {{ old('product_form') == 'Ground' ? 'selected' : '' }}>Ground</option>
                <option value="Instant" {{ old('product_form') == 'Instant' ? 'selected' : '' }}>Instant</option>
                <option value="Espresso" {{ old('product_form') == 'Espresso' ? 'selected' : '' }}>Espresso</option>
            </select>
            @error('product_form')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        {{-- Roast Level Field --}}
        <div class="mb-4">
            <label for="roast_level" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Roast Level
            </label>
            <select name="roast_level" 
                id="roast_level"
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-coffee-brown focus:border-coffee-brown dark:bg-dark-background dark:text-off-white">
                <option value="">Select roast level</option>
                <option value="Light" {{ old('roast_level') == 'Light' ? 'selected' : '' }}>Light</option>
                <option value="Medium-Light" {{ old('roast_level') == 'Medium-Light' ? 'selected' : '' }}>Medium-Light</option>
                <option value="Medium" {{ old('roast_level') == 'Medium' ? 'selected' : '' }}>Medium</option>
                <option value="Medium-Dark" {{ old('roast_level') == 'Medium-Dark' ? 'selected' : '' }}>Medium-Dark</option>
                <option value="Dark" {{ old('roast_level') == 'Dark' ? 'selected' : '' }}>Dark</option>
            </select>
            @error('roast_level')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </form>
</x-modal>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin inventory: DOMContentLoaded event triggered');
    
    // Get all view details buttons
    const buttons = document.querySelectorAll('.view-details-btn');
    console.log('Admin inventory: Found view details buttons:', buttons.length);
    
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    const itemDetailsContent = document.getElementById('itemDetailsContent');
    
    // Setup modal close functionality
    setupModalClose(viewDetailsModal);
    
    // Set up view details functionality
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            console.log('Admin inventory: View details button clicked!');
            
            const dataType = this.getAttribute('data-type');
            const dataId = this.getAttribute('data-id');
            
            let identifier;
            if (dataType === 'raw-coffee') {
                identifier = this.getAttribute('data-coffee-type');
            } else if (dataType === 'coffee-product') {
                identifier = this.getAttribute('data-product-name');
            }
            
            if (!identifier) {
                console.error('No identifier found for button');
                return;
            }
            
            // Show loading state
            itemDetailsContent.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div><p class="mt-2 text-gray-600">Loading details...</p></div>';
            
            // Open modal
            viewDetailsModal.classList.remove('hidden');
            viewDetailsModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
            
            // Fetch details from the server
            const endpoint = dataType === 'raw-coffee' 
                ? `/inventory/details/${encodeURIComponent(identifier)}`
                : `/inventory/product-details/${encodeURIComponent(identifier)}`;
                
            fetch(endpoint)
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
                                <h3 class="text-xl font-semibold text-gray-900">${data.name || identifier}</h3>
                                <div class="mt-2">
                                    <div class="text-3xl font-bold text-light-brown mt-1">${data.total_quantity || 0} ${dataType === 'raw-coffee' ? 'kg' : 'units'}</div>
                                </div>
                            </div>
                            
                            <!-- Details Breakdown -->
                            <div>
                                <h4 class="text-lg font-medium text-gray-900 mb-3">${dataType === 'raw-coffee' ? 'Grade Breakdown' : 'Location Breakdown'}</h4>
                                ${data.breakdown && data.breakdown.length > 0 ? `
                                    <div class="space-y-4">
                                        ${data.breakdown.map(item => `
                                            <div class="border border-gray-200 rounded-lg p-4">
                                                <div class="flex justify-between items-center mb-3">
                                                    <h5 class="text-lg font-medium text-gray-900">${dataType === 'raw-coffee' ? 'Grade: ' + item.grade : 'Location: ' + item.location}</h5>
                                                    <span class="text-xl font-bold text-light-brown">${item.total_quantity} ${dataType === 'raw-coffee' ? 'kg' : 'units'}</span>
                                                </div>
                                                
                                                ${item.details && item.details.length > 0 ? `
                                                    <div class="overflow-x-auto">
                                                        <table class="min-w-full divide-y divide-gray-200">
                                                            <thead class="bg-gray-50">
                                                                <tr>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">${dataType === 'raw-coffee' ? 'Warehouse' : 'Category'}</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="bg-white divide-y divide-gray-200">
                                                                ${item.details.map(detail => `
                                                                    <tr>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">${detail.location || detail.category}</td>
                                                                        <td class="px-4 py-2 text-sm text-gray-900">${detail.quantity} ${dataType === 'raw-coffee' ? 'kg' : 'units'}</td>
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
                                                    <p class="text-gray-500 text-center py-4">No inventory found for this ${dataType === 'raw-coffee' ? 'grade' : 'category'}</p>
                                                `}
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : `
                                    <div class="text-center py-8 text-gray-500">
                                        <p>No inventory records found for this ${dataType === 'raw-coffee' ? 'coffee type' : 'product'}.</p>
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
    
    // Modal close functionality
    function setupModalClose(modal) {
        if (!modal) return;
        
        // Close on backdrop click
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal(modal);
            }
        });
        
        // Close on cancel button
        const cancelButton = modal.querySelector('button[data-modal-close]');
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                closeModal(modal);
            });
        }
    }
    
    function closeModal(modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = 'auto';
    }
});

// Edit inventory item function
function editInventoryItem(itemId) {
    console.log('Editing inventory item:', itemId);
    
    // Show loading overlay
    const loadingOverlay = document.createElement('div');
    loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
    loadingOverlay.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i> Loading...</div>';
    document.body.appendChild(loadingOverlay);
    
    // Close the view details modal
    const viewDetailsModal = document.getElementById('viewDetailsModal');
    viewDetailsModal.classList.add('hidden');
    viewDetailsModal.classList.remove('flex');
    
    // Redirect to edit page or open edit modal
    window.location.href = `/inventory/${itemId}/edit`;
}

// Delete inventory item function
function confirmDeleteItem(itemId) {
    console.log('Attempting to delete inventory item:', itemId);
    
    if (confirm('Are you sure you want to delete this inventory item? This action cannot be undone.')) {
        // Show loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.className = 'fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50';
        loadingOverlay.innerHTML = '<div class="bg-white p-4 rounded-lg shadow-lg"><i class="fas fa-spinner fa-spin text-blue-600 mr-2"></i> Deleting...</div>';
        document.body.appendChild(loadingOverlay);
        
        // Create and submit a form to delete the item
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/inventory/${itemId}`;
        
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
