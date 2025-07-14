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
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
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
      title="Mountain Blend"
      :value="number_format($mountainBrewQuantity, 2)"
      unit="kg"
      iconClass="fa-cube"
    />
    <x-stats-card
      title="Morning Brew"
      :value="number_format($morningBrewQuantity, 2)"
      unit="kg"
      iconClass="fa-cube"
    />
  </div>

  <!-- Inventory Table -->
  <div class="mt-10">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-semibold text-dashboard-light">Inventory Items</h2>
      <button class="bg-light-brown text-white px-4 py-2 rounded" data-modal-open="addItemModal">+ Add Item</button>
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
                  data-id="{{ $item->id }}">
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

<!-- Add/Edit Item Modal -->
<x-modal id="addItemModal" title="Add New Item" size="md">
  <form id="inventoryForm" method="POST" action="{{ route('supplierInventory.store') }}">
    @csrf
    <input type="hidden" name="_method" value="POST">

    <div class="space-y-4">
      <!-- Product Selection -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
        <select name="product_id" class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
          <option value="">Select a product</option>
          @foreach($products as $product)
            <option value="{{ $product->id }}">{{ $product->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Quantity -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (kg)</label>
        <input 
          type="number" 
          name="quantity" 
          step="0.01" 
          class="w-full rounded border-gray-300 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
          placeholder="Enter quantity">
      </div>

      <div class="flex justify-end space-x-3 pt-4">
        <button type="button" class="px-4 py-2 text-gray-600 bg-gray-100 rounded hover:bg-gray-200" data-modal-close>Cancel</button>
        <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Save</button>
      </div>
    </div>
  </form>
</x-modal>

<!-- View Details Modal -->
<x-modal id="viewDetailsModal" title="Item Details" size="md">
  <div id="itemDetailsContent" class="space-y-4">
    <!-- Content will be loaded dynamically -->
  </div>
  <div class="flex justify-end pt-4">
    <button type="button" class="px-4 py-2 text-gray-600 bg-gray-100 rounded hover:bg-gray-200" data-modal-close>Close</button>
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