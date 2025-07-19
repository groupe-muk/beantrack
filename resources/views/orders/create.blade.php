@extends('layouts.main-view')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-7 text-dashboard-light sm:text-3xl">
                        Create New Order
                    </h2>
                    <p class="mt-1 text-sm text-soft-brown">
                        Create an order for raw coffee from suppliers
                    </p>
                </div>
                <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 border rounded-md shadow-sm text-sm font-medium text-white bg-light-brown hover:bg-brown focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
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

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">
                                Please fix the following errors:
                            </h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul role="list" class="list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Success Message -->
            @if (session('success'))
                <div id="success-alert" class="mx-6 mt-4 bg-green-50 border border-green-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L8.53 10.53a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <form id="order-form" action="{{ route('orders.store') }}" method="POST" class="p-6 space-y-6">
                @csrf
                
                <!-- Supplier Selection -->
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-dashboard-light mb-2">
                        Supplier <span class="text-red-500">*</span>
                    </label>
                    <select id="supplier_id" name="supplier_id" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm"
                            onchange="updateSupplierInfo()">
                        <option value="">Select a supplier</option>
                        @foreach($suppliers as $id => $name)
                            <option value="{{ $id }}" {{ old('supplier_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grade Selection -->
                <div>
                    <label for="grade" class="block text-sm font-medium text-dashboard-light mb-2">
                        Coffee Grade <span class="text-red-500">*</span>
                    </label>
                    <select id="grade" name="grade" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm"
                            onchange="updatePriceCalculation()">
                        <option value="">Select grade</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade }}" {{ old('grade') == $grade ? 'selected' : '' }}>{{ $grade }}</option>
                        @endforeach
                    </select>
                    @error('grade')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Coffee Type Selection -->
                <div>
                    <label for="coffee_type" class="block text-sm font-medium text-dashboard-light mb-2">
                        Coffee Type <span class="text-red-500">*</span>
                    </label>
                    <select id="coffee_type" name="coffee_type" required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm"
                            onchange="updatePriceCalculation()">
                        <option value="">Select coffee type</option>
                        @foreach($coffeeTypes as $type)
                            <option value="{{ $type }}" {{ old('coffee_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('coffee_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Order Date (Auto-set to today) -->
                <div>
                    <label for="order_date" class="block text-sm font-medium text-dashboard-light mb-2">
                        Order Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="order_date" name="order_date" required
                           value="{{ old('order_date', date('Y-m-d')) }}" 
                           class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-light-brown focus:border-light-brown sm:text-sm">
                    @error('order_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Date is automatically set to today</p>
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

                <!-- Price Calculation Display -->
                <div id="price-calculation" class="hidden bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="text-sm font-medium text-blue-800 mb-2">Price Calculation</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-blue-700">Grade:</span>
                            <span id="display-grade" class="text-blue-900 font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Coffee Type:</span>
                            <span id="display-coffee-type" class="text-blue-900 font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-700">Base Price per kg:</span>
                            <span id="base-price" class="text-blue-900 font-medium">$0.00</span>
                        </div>
                        <div class="flex justify-between border-t border-blue-200 pt-2">
                            <span class="text-blue-700 font-medium">Total Amount:</span>
                            <span id="total-price" class="text-blue-900 font-bold text-lg">$0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Hidden field for calculated total_amount -->
                <input type="hidden" id="total_amount" name="total_amount" value="{{ old('total_amount') }}">

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
                        <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-yellow-900 to-yellow-800 hover:from-yellow-800 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Create Order
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateSupplierInfo() {
    const select = document.getElementById('supplier_id');
    if (select.value) {
        // Could add supplier-specific information display here if needed
        updatePriceCalculation();
    } else {
        hidePriceCalculation();
    }
}

function updatePriceCalculation() {
    const grade = document.getElementById('grade').value;
    const coffeeType = document.getElementById('coffee_type').value;
    const priceCalculation = document.getElementById('price-calculation');
    
    if (grade && coffeeType) {
        // Update display fields
        document.getElementById('display-grade').textContent = grade;
        document.getElementById('display-coffee-type').textContent = coffeeType;
        
        priceCalculation.classList.remove('hidden');
        calculateTotal();
    } else {
        hidePriceCalculation();
    }
}

function hidePriceCalculation() {
    const priceCalculation = document.getElementById('price-calculation');
    priceCalculation.classList.add('hidden');
    
    // Reset price display
    document.getElementById('base-price').textContent = '$0.00';
    document.getElementById('total-price').textContent = '$0.00';
    document.getElementById('total_amount').value = '';
}

function calculateTotal() {
    const grade = document.getElementById('grade').value;
    const coffeeType = document.getElementById('coffee_type').value;
    const quantity = parseFloat(document.getElementById('quantity').value) || 0;
    
    if (!grade || !coffeeType || !quantity) {
        return;
    }
    
    // Base price calculation for raw coffee (simpler than coffee products)
    let basePrice = 2.50; // Default base price for raw coffee
    
    // Grade-based pricing
    switch(grade.toLowerCase()) {
        case 'aa':
        case 'premium':
            basePrice = 3.50;
            break;
        case 'ab':
        case 'high':
            basePrice = 3.00;
            break;
        case 'c':
        case 'standard':
            basePrice = 2.50;
            break;
        case 'pb':
        case 'low':
            basePrice = 2.00;
            break;
        default:
            basePrice = 2.50;
            break;
    }
    
    // Coffee type adjustments
    switch(coffeeType.toLowerCase()) {
        case 'arabica':
            basePrice *= 1.2; // 20% premium for arabica
            break;
        case 'robusta':
            basePrice *= 1.0; // No adjustment for robusta
            break;
        case 'excella':
        case 'specialty':
            basePrice *= 1.3; // 30% premium for specialty
            break;
        default:
            basePrice *= 1.0;
            break;
    }
    
    const totalPrice = Math.round(basePrice * quantity); // Round to whole number
    
    // Update display
    document.getElementById('base-price').textContent = `$${basePrice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    document.getElementById('total-price').textContent = `$${totalPrice.toLocaleString('en-US', {minimumFractionDigits: 0, maximumFractionDigits: 0})}`;
    
    // Update hidden field for form submission
    document.getElementById('total_amount').value = totalPrice;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set date to today if not already set
    const orderDate = document.getElementById('order_date');
    if (!orderDate.value) {
        orderDate.value = new Date().toISOString().split('T')[0];
    }
    
    // Initialize price calculation if values are present
    updatePriceCalculation();
    
    // Clear form when success message is shown
    @if (session('success'))
        // Clear the form
        document.getElementById('order-form').reset();
        
        // Reset date to today after clearing
        orderDate.value = new Date().toISOString().split('T')[0];
        
        // Auto-hide success alert after 5 seconds
        setTimeout(function() {
            const alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }
        }, 5000);
    @endif
});
</script>

@endsection
