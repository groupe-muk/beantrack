@extends('layouts.main-view')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-7 text-dashboard-light sm:text-3xl">
                        Place New Order
                    </h2>
                    <p class="mt-1 text-sm text-soft-brown">
                        Select a coffee product and specify the quantity you need
                    </p>
                </div>
                <a href="{{ route('orders.vendor.index') }}" class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium text-white bg-light-brown hover:bg-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Orders
                </a>
            </div>
        </div>

        <!-- Order Form -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-dashboard-light">Order Details</h3>
                <p class="mt-1 text-sm text-soft-brown">Fill in the details for your new order</p>
            </div>

            <form action="{{ route('orders.vendor.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <!-- Product Selection -->
                <div>
                    <label for="coffee_product_id" class="block text-sm font-medium text-dashboard-light mb-2">
                        Coffee Product <span class="text-red-500">*</span>
                    </label>
                    <select name="coffee_product_id" id="coffee_product_id" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm"
                            onchange="updateProductInfo()">
                        <option value="">Select a coffee product</option>
                        @foreach($coffeeProducts as $product)
                            <option value="{{ $product->id }}" 
                                    data-name="{{ $product->name }}"
                                    data-category="{{ $product->category }}"
                                    data-roast="{{ $product->roast_level }}"
                                    data-form="{{ $product->product_form }}"
                                    data-raw-coffee="{{ $product->rawCoffee ? $product->rawCoffee->grade . ' - ' . $product->rawCoffee->coffee_type : 'N/A' }}"
                                    {{ old('coffee_product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} - {{ $product->category }}
                            </option>
                        @endforeach
                    </select>
                    @error('coffee_product_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Product Info Display -->
                <div id="product-info" class="hidden bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg p-4 border border-yellow-200">
                    <h4 class="text-sm font-medium text-yellow-800 mb-2">Product Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-yellow-700 font-medium">Category:</span>
                            <span id="product-category" class="text-yellow-900 ml-1"></span>
                        </div>
                        <div>
                            <span class="text-yellow-700 font-medium">Roast Level:</span>
                            <span id="product-roast" class="text-yellow-900 ml-1"></span>
                        </div>
                        <div>
                            <span class="text-yellow-700 font-medium">Form:</span>
                            <span id="product-form" class="text-yellow-900 ml-1"></span>
                        </div>
                        <div>
                            <span class="text-yellow-700 font-medium">Raw Coffee:</span>
                            <span id="product-raw-coffee" class="text-yellow-900 ml-1"></span>
                        </div>
                    </div>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="quantity" class="block text-sm font-medium text-dashboard-light mb-2">
                        Quantity (kg) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="quantity" id="quantity" required min="1" step="0.01"
                               value="{{ old('quantity') }}" 
                               class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm"
                               placeholder="Enter quantity in kg"
                               oninput="calculateTotal()">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">kg</span>
                        </div>
                    </div>
                    @error('quantity')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price Calculation -->
                <div id="price-calculation" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Price Calculation</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Base Price per kg:</span>
                            <span id="base-price" class="text-blue-900 font-medium">$1,000.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Markup (20%):</span>
                            <span id="markup-amount" class="text-blue-900 font-medium">$200.00</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Price per kg:</span>
                            <span id="price-per-kg" class="text-blue-900 font-medium">$1,200.00</span>
                        </div>
                        <div class="flex justify-between border-t border-blue-200 pt-2">
                            <span class="text-blue-700 font-medium">Total Price:</span>
                            <span id="total-price" class="text-blue-900 font-bold text-lg">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-dashboard-light mb-2">
                        Notes (Optional)
                    </label>
                    <textarea name="notes" id="notes" rows="4" 
                              class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm"
                              placeholder="Any special instructions or notes for this order...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-6 border-t border-gray-200">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('orders.vendor.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-yellow-900 to-yellow-800 hover:from-yellow-800 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Place Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateProductInfo() {
    const select = document.getElementById('coffee_product_id');
    const productInfo = document.getElementById('product-info');
    const priceCalculation = document.getElementById('price-calculation');
    
    if (select.value) {
        const option = select.options[select.selectedIndex];
        
        // Update product info
        document.getElementById('product-category').textContent = option.dataset.category || 'N/A';
        document.getElementById('product-roast').textContent = option.dataset.roast || 'N/A';
        document.getElementById('product-form').textContent = option.dataset.form || 'N/A';
        document.getElementById('product-raw-coffee').textContent = option.dataset.rawCoffee || 'N/A';
        
        productInfo.classList.remove('hidden');
        priceCalculation.classList.remove('hidden');
        
        // Calculate price immediately when product is selected
        calculateTotal();
    } else {
        productInfo.classList.add('hidden');
        priceCalculation.classList.add('hidden');
        
        // Reset price display
        document.getElementById('base-price').textContent = '$0.00';
        document.getElementById('markup-amount').textContent = '$0.00';
        document.getElementById('price-per-kg').textContent = '$0.00';
        document.getElementById('total-price').textContent = '$0.00';
    }
}

function calculateTotal() {
    const select = document.getElementById('coffee_product_id');
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    if (!select.value || !quantity) {
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const category = option.dataset.category?.toLowerCase() || 'standard';
    const roastLevel = option.dataset.roast?.toLowerCase() || 'light';
    
    // Base price calculation matching backend logic
    let basePrice = 3.04; // Default for standard
    switch(category) {
        case 'premium':
            basePrice = 5.04;
            break;
        case 'specialty':
            basePrice = 4.20;
            break;
        case 'standard':
        default:
            basePrice = 3.04;
            break;
    }
    
    // Roast level adjustments matching backend logic
    switch(roastLevel) {
        case 'dark':
            basePrice *= 1.1; // 10% increase for dark roast
            break;
        case 'medium':
            basePrice *= 1.05; // 5% increase for medium roast
            break;
        case 'light':
        default:
            // No adjustment for light roast
            break;
    }
    
    const markupRate = 0.20; // 20% markup
    const markupAmount = basePrice * markupRate;
    const pricePerKg = basePrice + markupAmount;
    const totalPrice = Math.round(pricePerKg * quantity); // Round to match backend
    
    // Update display
    document.getElementById('base-price').textContent = `$${basePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('markup-amount').textContent = `$${markupAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('price-per-kg').textContent = `$${pricePerKg.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('total-price').textContent = `$${totalPrice.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateProductInfo();
});
</script>
@endsection
