@extends('layouts.main-view')

@section('content')

@php
    $statuses = [
        'pending' => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'processing' => 'bg-indigo-100 text-indigo-800',
        'shipped' => 'bg-purple-100 text-purple-800',
        'delivered' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    
    $iconColors = [
        'pending' => 'bg-yellow-100 text-yellow-600',
        'confirmed' => 'bg-blue-100 text-blue-600',
        'processing' => 'bg-indigo-100 text-indigo-600',
        'shipped' => 'bg-purple-100 text-purple-600',
        'delivered' => 'bg-green-100 text-green-600',
        'cancelled' => 'bg-red-100 text-red-600',
    ];
@endphp

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Navigation -->
        <div class="mb-6">
            <a href="{{ route('orders.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-light-brown hover:bg-brown text-white text-sm font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Orders
            </a>
        </div>

        <!-- Header -->
        <div class="mb-8">
            <div class="bg-white rounded-xl shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-dashboard-light mb-2">Order #{{ $order->id }}</h1>
                        <p class="text-soft-brown">Order details and management actions</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Order Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Information -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-dashboard-light mb-4">Order Information</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Customer/Supplier Info -->
                        <div>
                            @if($order->wholesaler)
                                <h3 class="text-sm font-medium text-soft-brown mb-2">Vendor Customer</h3>
                                <p class="text-lg font-semibold text-dashboard-light">{{ $order->wholesaler->name }}</p>
                                <p class="text-sm text-soft-brown">Order from vendor</p>
                            @elseif($order->supplier)
                                <h3 class="text-sm font-medium text-soft-brown mb-2">Supplier</h3>
                                <p class="text-lg font-semibold text-dashboard-light">{{ $order->supplier->name }}</p>
                                <p class="text-sm text-soft-brown">Order to supplier</p>
                            @else
                                <h3 class="text-sm font-medium text-soft-brown mb-2">Customer/Supplier</h3>
                                <p class="text-lg font-semibold text-dashboard-light">N/A</p>
                            @endif
                        </div>
                        
                        <!-- Product Info -->
                        <div>
                            @if($order->coffeeProduct)
                                <h3 class="text-sm font-medium text-soft-brown mb-2">Coffee Product</h3>
                                <p class="text-lg font-semibold text-dashboard-light">{{ $order->coffeeProduct->name }}</p>
                                <p class="text-sm text-soft-brown">Category: {{ $order->coffeeProduct->category }}</p>
                                <p class="text-sm text-soft-brown">Form: {{ $order->coffeeProduct->product_form }}</p>
                                @if($order->coffeeProduct->roast_level)
                                    <p class="text-sm text-soft-brown">Roast: {{ $order->coffeeProduct->roast_level }}</p>
                                @endif
                            @elseif($order->rawCoffee)
                                <h3 class="text-sm font-medium text-soft-brown mb-2">Raw Coffee</h3>
                                <p class="text-lg font-semibold text-dashboard-light">{{ $order->rawCoffee->coffee_type }}</p>
                                <p class="text-sm text-soft-brown">Grade: {{ $order->rawCoffee->grade }}</p>
                                @if($order->rawCoffee->screen_size)
                                    <p class="text-sm text-soft-brown">Screen Size: {{ $order->rawCoffee->screen_size }}</p>
                                @endif
                            @else
                                <h3 class="text-sm font-medium text-soft-brown mb-2">Coffee Product</h3>
                                <p class="text-lg font-semibold text-dashboard-light">N/A</p>
                            @endif
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Quantity</h3>
                            <p class="text-lg font-semibold text-dashboard-light">{{ number_format($order->quantity) }} kg</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Available Stock</h3>
                            @if($order->coffeeProduct)
                                @php
                                    $availableStock = \App\Models\Inventory::getAvailableStockByType('coffee_product', $order->coffee_product_id);
                                    $isStockSufficient = $availableStock >= $order->quantity;
                                @endphp
                                <p class="text-lg font-semibold {{ $isStockSufficient ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($availableStock) }} kg
                                </p>
                                @if(!$isStockSufficient)
                                    <p class="text-xs text-red-500">Short: {{ number_format($order->quantity - $availableStock) }} kg</p>
                                @else
                                    <p class="text-xs text-green-500">✓ Sufficient stock</p>
                                @endif
                            @elseif($order->rawCoffee)
                                @php
                                    $availableStock = \App\Models\Inventory::getAvailableStock($order->raw_coffee_id);
                                    $isStockSufficient = $availableStock >= $order->quantity;
                                @endphp
                                <p class="text-lg font-semibold {{ $isStockSufficient ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format($availableStock) }} kg
                                </p>
                                @if(!$isStockSufficient)
                                    <p class="text-xs text-red-500">Short: {{ number_format($order->quantity - $availableStock) }} kg</p>
                                @else
                                    <p class="text-xs text-green-500">✓ Sufficient stock</p>
                                @endif
                            @else
                                <p class="text-lg font-semibold text-gray-400">N/A</p>
                            @endif
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Unit Price</h3>
                            <p class="text-lg font-semibold text-dashboard-light">${{ $order->quantity > 0 ? number_format($order->total_price / $order->quantity, 0) : '0.00' }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Total Price</h3>
                            <p class="text-lg font-semibold text-green-600">${{ number_format($order->total_price, 0) }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Order Date</h3>
                            <p class="text-lg font-semibold text-dashboard-light">{{ $order->created_at ? $order->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}</p>
                        </div>
                    </div>

                    @if($order->notes)
                        <div class="mt-6">
                            <h3 class="text-sm font-medium text-light-brown mb-2">Notes</h3>
                            <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $order->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Order Tracking -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-dashboard-light mb-4">Order Tracking</h2>
                    
                    @if($order->orderTrackings->count() > 0)
                        <div class="space-y-4">
                            @foreach($order->orderTrackings->sortByDesc('created_at') as $tracking)
                                <div class="flex items-start space-x-4 pb-4 border-b border-gray-200 last:border-b-0">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 {{ $iconColors[$tracking->status] ?? 'bg-gray-100 text-gray-600' }} rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium text-gray-900">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statuses[$tracking->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($tracking->status) }}
                                                </span>
                                            </h3>
                                            <span class="text-sm text-gray-500">{{ $tracking->created_at ? $tracking->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}</span>
                                        </div>
                                        @if($tracking->notes)
                                            <p class="text-sm text-gray-600 mt-1">{{ $tracking->notes }}</p>
                                        @endif
                                        @if($tracking->location)
                                            <p class="text-xs text-gray-500 mt-1">📍 {{ $tracking->location }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500">No tracking information available.</p>
                    @endif
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-dashboard-light mb-4">Quick Actions</h2>
                    
                    <div class="space-y-3">
                        @if($order->wholesaler && $order->status === 'pending')
                            <!-- Actions for orders from vendors -->
                            <button onclick="acceptVendorOrder('{{ $order->id }}')"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                Accept Vendor Order
                            </button>
                            
                            <button onclick="rejectVendorOrder('{{ $order->id }}')"
                                    class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                Reject Order
                            </button>
                        @endif
                        
                        @if($order->supplier && $order->status === 'confirmed')
                            <!-- Actions for orders to suppliers -->
                            <form action="{{ route('orders.update-status', $order) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="shipped">
                                <button type="submit" 
                                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                                        onclick="return confirm('Mark this order as shipped?')">
                                    Mark as Shipped
                                </button>
                            </form>
                        @endif
                        
                        @if($order->supplier && $order->status === 'shipped')
                            <!-- Actions for shipped supplier orders -->
                            <form action="{{ route('orders.update-status', $order) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="delivered">
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                                        onclick="return confirm('Confirm that this order has been received?')">
                                    Mark as Received
                                </button>
                            </form>
                        @endif
                        
                        @if(in_array($order->status, ['delivered', 'cancelled']))
                            <div class="bg-gray-100 text-gray-600 text-center py-2 px-4 rounded-lg">
                                No actions available
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Subtotal:</span>
                            <span class="font-medium">${{ number_format($order->total_price, 2) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">Shipping:</span>
                            <span class="font-medium">Included</span>
                        </div>
                        <div class="border-t pt-3">
                            <div class="flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-900">Total:</span>
                                <span class="text-lg font-bold text-green-600">${{ number_format($order->total_price, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

@if(session('success'))
    <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg z-50" id="success-alert">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ session('success') }}</p>
            </div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-lg z-50" id="error-alert">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium">{{ session('error') }}</p>
            </div>
        </div>
    </div>
@endif

               
               
            </div>

           

        </div>
    </div>
</div>

<!-- Status Update Modal (same as in index) -->
<div id="statusModal" class="hidden fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div>
                <div class="mt-3 text-center sm:mt-5">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        Update Order Status
                    </h3>
                    <div class="mt-2">
                        <form id="statusForm" method="POST" action="">
                            @csrf
                            <input type="hidden" name="_method" value="PUT">
                            <div class="mt-4">
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" onchange="checkInventoryAvailability()">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <div id="inventoryWarning" class="hidden mt-2 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-md">
                                    <p class="text-sm font-medium">⚠️ Inventory Check</p>
                                    <p id="inventoryMessage" class="text-sm mt-1"></p>
                                </div>
                                <div id="inventoryError" class="hidden mt-2 p-3 bg-red-100 border border-red-400 text-red-700 rounded-md">
                                    <p class="text-sm font-medium">❌ Insufficient Inventory</p>
                                    <p id="inventoryErrorMessage" class="text-sm mt-1"></p>
                                </div>
                            </div>
                            <div class="mt-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Add any additional notes here..."></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                <button type="button" id="updateButton" onclick="submitStatusUpdate();" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:col-start-2 sm:text-sm">
                    Update
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let inventoryChecked = false;
    let inventoryAvailable = false;

    // Function to open the status update modal
    function updateStatus(orderId) {
        const form = document.getElementById('statusForm');
        form.action = `/orders/${orderId}/status`;
        document.getElementById('statusModal').classList.remove('hidden');
        
        // Reset inventory check state
        inventoryChecked = false;
        inventoryAvailable = false;
        hideInventoryMessages();
    }

    // Function to close the modal
    function closeModal() {
        document.getElementById('statusModal').classList.add('hidden');
        hideInventoryMessages();
    }

    // Function to check inventory availability when status changes
    function checkInventoryAvailability() {
        const selectedStatus = document.getElementById('status').value;
        const currentStatus = '{{ $order->status }}';
        
        hideInventoryMessages();
        
        // Only check inventory when changing from 'pending' to 'confirmed'
        if (selectedStatus === 'confirmed' && currentStatus === 'pending') {
            // Show loading state
            showInventoryWarning('Checking inventory availability...');
            
            fetch(`/orders/{{ $order->id }}/check-inventory`)
                .then(response => response.json())
                .then(data => {
                    inventoryChecked = true;
                    inventoryAvailable = data.available;
                    
                    if (data.available) {
                        showInventoryWarning(`✅ ${data.message}`);
                        enableUpdateButton();
                    } else {
                        showInventoryError(data.message);
                        disableUpdateButton();
                    }
                })
                .catch(error => {
                    console.error('Error checking inventory:', error);
                    showInventoryError('Failed to check inventory availability. Please try again.');
                    disableUpdateButton();
                });
        } else {
            // For other status changes, enable the button
            inventoryChecked = true;
            inventoryAvailable = true;
            enableUpdateButton();
        }
    }

    // Function to submit status update with inventory validation
    function submitStatusUpdate() {
        const selectedStatus = document.getElementById('status').value;
        const currentStatus = '{{ $order->status }}';
        
        // For confirmed status from pending, check if inventory was validated
        if (selectedStatus === 'confirmed' && currentStatus === 'pending') {
            if (!inventoryChecked) {
                alert('Please wait for inventory check to complete.');
                return;
            }
            
            if (!inventoryAvailable) {
                alert('Cannot confirm order due to insufficient inventory.');
                return;
            }
            
            // Additional confirmation for inventory reduction
            if (!confirm('This will reduce the inventory levels. Are you sure you want to confirm this order?')) {
                return;
            }
        }
        
        document.getElementById('statusForm').submit();
    }

    // Helper functions for UI management
    function showInventoryWarning(message) {
        hideInventoryMessages();
        document.getElementById('inventoryMessage').textContent = message;
        document.getElementById('inventoryWarning').classList.remove('hidden');
    }

    function showInventoryError(message) {
        hideInventoryMessages();
        document.getElementById('inventoryErrorMessage').textContent = message;
        document.getElementById('inventoryError').classList.remove('hidden');
    }

    function hideInventoryMessages() {
        document.getElementById('inventoryWarning').classList.add('hidden');
        document.getElementById('inventoryError').classList.add('hidden');
    }

    function enableUpdateButton() {
        const button = document.getElementById('updateButton');
        button.disabled = false;
        button.classList.remove('opacity-50', 'cursor-not-allowed');
        button.classList.add('hover:bg-indigo-700');
    }

    function disableUpdateButton() {
        const button = document.getElementById('updateButton');
        button.disabled = true;
        button.classList.add('opacity-50', 'cursor-not-allowed');
        button.classList.remove('hover:bg-indigo-700');
    }

    // Function to accept vendor order
    function acceptVendorOrder(orderId) {
        if (confirm('Are you sure you want to accept this vendor order?')) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/orders/' + orderId + '/accept-vendor';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PUT';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Function to reject vendor order
    function rejectVendorOrder(orderId) {
        if (confirm('Are you sure you want to reject this vendor order?')) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/orders/' + orderId + '/reject-vendor';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'PUT';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
</script>
@endpush
@endsection
