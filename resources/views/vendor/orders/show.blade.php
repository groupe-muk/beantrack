@extends('layouts.main-view')

@section('content')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- Navigation -->
        <div class="mb-6">
            <a href="{{ route('orders.vendor.index') }}" 
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
                        <p class="text-soft-brown">Order details and tracking information</p>
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
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Coffee Product</h3>
                            @if($order->coffeeProduct)
                                <p class="text-lg font-semibold text-dashboard-light">{{ $order->coffeeProduct->name }}</p>
                                <p class="text-sm text-soft-brown">Category: {{ $order->coffeeProduct->category }}</p>
                                <p class="text-sm text-soft-brown">Form: {{ $order->coffeeProduct->product_form }}</p>
                                @if($order->coffeeProduct->roast_level)
                                    <p class="text-sm text-soft-brown">Roast: {{ $order->coffeeProduct->roast_level }}</p>
                                @endif
                            @else
                                <p class="text-lg font-semibold text-dashboard-light">N/A</p>
                            @endif
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Raw Coffee Source</h3>
                            @if($order->coffeeProduct && $order->coffeeProduct->rawCoffee)
                                <p class="text-lg font-semibold text-dashboard-light">{{ $order->coffeeProduct->rawCoffee->coffee_type }}</p>
                                <p class="text-sm text-soft-brown">Grade: {{ $order->coffeeProduct->rawCoffee->grade }}</p>
                                @if($order->coffeeProduct->rawCoffee->screen_size)
                                    <p class="text-sm text-soft-brown">Screen Size: {{ $order->coffeeProduct->rawCoffee->screen_size }}</p>
                                @endif
                            @else
                                <p class="text-lg font-semibold text-dashboard-light">N/A</p>
                            @endif
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Quantity</h3>
                            <p class="text-lg font-semibold text-dashboard-light">{{ number_format($order->quantity) }} kgs</p>
                        </div>
                        
                        <div>
                            <h3 class="text-sm font-medium text-soft-brown mb-2">Unit Price</h3>
                            <p class="text-lg font-semibold text-dashboard-light">${{ $order->quantity > 0 ? number_format($order->total_price / $order->quantity, 2) : '0.00' }}</p>
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
                                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        @if($order->status === 'pending')
                            <form action="{{ route('orders.vendor.cancel', $order) }}" method="POST" class="w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                                        onclick="return confirm('Are you sure you want to cancel this order?')">
                                    Cancel Order
                                </button>
                            </form>
                        @endif
                        
                        @if($order->status === 'shipped')
                            <form action="{{ route('orders.vendor.received', $order) }}" method="POST" class="w-full">
                                @csrf
                                @method('PATCH')
                                <button type="submit" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200"
                                        onclick="return confirm('Confirm that you have received this order?')">
                                    Mark as Received
                                </button>
                            </form>
                        @endif
                        
                        @if(in_array($order->status, ['confirmed', 'delivered', 'cancelled']))
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

<script>
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const successAlert = document.getElementById('success-alert');
        const errorAlert = document.getElementById('error-alert');
        if (successAlert) successAlert.style.display = 'none';
        if (errorAlert) errorAlert.style.display = 'none';
    }, 5000);
</script>
@endsection