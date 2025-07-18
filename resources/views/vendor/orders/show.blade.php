@extends('layouts.main-view')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                {{ session('error') }}
            </div>
        @endif
        
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-7 text-dashboard-light sm:text-3xl">
                        Order Details
                    </h2>
                    <p class="mt-1 text-sm text-soft-brown">
                        View and track your order #{{ $order->id }}
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Order Information -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-dashboard-light">Order Information</h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Order Status -->
                        <div class="flex items-center justify-between">
                            <div>
                                <dt class="text-sm font-medium text-soft-brown">Order Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </dd>
                            </div>
                            
                            <!-- Action Buttons based on status -->
                            <div class="flex space-x-3">
                                @if($order->status === 'pending')
                                    <form action="{{ route('orders.vendor.cancel', $order) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-200" onclick="return confirm('Are you sure you want to cancel this order?')">
                                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                            Cancel Order
                                        </button>
                                    </form>
                                @elseif($order->status === 'shipped')
                                    <form action="{{ route('orders.vendor.received', $order) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-white hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200" onclick="return confirm('Confirm that you have received this order?')">
                                            <svg class="-ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Mark as Received
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        <!-- Product Details -->
                        <div>
                            <dt class="text-sm font-medium text-soft-brown">Product</dt>
                            <dd class="mt-1">
                                @if($order->coffeeProduct)
                                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 rounded-lg p-4 border border-yellow-200">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <h4 class="text-lg font-medium text-yellow-900">{{ $order->coffeeProduct->name }}</h4>
                                                <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <span class="text-yellow-700 font-medium">Category:</span>
                                                        <span class="text-yellow-900 ml-1">{{ $order->coffeeProduct->category ?? 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-yellow-700 font-medium">Roast Level:</span>
                                                        <span class="text-yellow-900 ml-1">{{ $order->coffeeProduct->roast_level ?? 'N/A' }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-yellow-700 font-medium">Form:</span>
                                                        <span class="text-yellow-900 ml-1">{{ $order->coffeeProduct->product_form ?? 'N/A' }}</span>
                                                    </div>
                                                    @if($order->coffeeProduct->rawCoffee)
                                                        <div>
                                                            <span class="text-yellow-700 font-medium">Raw Coffee:</span>
                                                            <span class="text-yellow-900 ml-1">{{ $order->coffeeProduct->rawCoffee->grade }} - {{ $order->coffeeProduct->rawCoffee->coffee_type }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-500">Product not available</span>
                                @endif
                            </dd>
                        </div>

                        <!-- Order Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-soft-brown">Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($order->quantity, 2) }} kg</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-soft-brown">Total Price</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">${{ number_format($order->total_price, 0) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-soft-brown">Order Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->order_date ? $order->order_date->format('F j, Y') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-soft-brown">Order ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $order->id }}</dd>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($order->notes)
                            <div>
                                <dt class="text-sm font-medium text-soft-brown">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $order->notes }}</dd>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Order Tracking -->
            <div class="lg:col-span-1">
                <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-dashboard-light">Order Tracking</h3>
                    </div>

                    <div class="p-6">
                        @if($order->orderTrackings && $order->orderTrackings->count() > 0)
                            <div class="flow-root">
                                <ul class="-mb-8">
                                    @foreach($order->orderTrackings->sortBy('created_at') as $tracking)
                                        <li>
                                            <div class="relative pb-8">
                                                @if(!$loop->last)
                                                    <span class="absolute top-5 left-5 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                                @endif
                                                <div class="relative flex items-start space-x-3">
                                                    <div class="relative">
                                                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-yellow-400 to-yellow-600 flex items-center justify-center ring-8 ring-white">
                                                            @if($tracking->status === 'shipped')
                                                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4-8-4m16 0v10l-8 4-8-4V7" />
                                                                </svg>
                                                            @elseif($tracking->status === 'delivered')
                                                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @else
                                                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <div>
                                                            <div class="text-sm">
                                                                <span class="font-medium text-gray-900">{{ ucfirst($tracking->status) }}</span>
                                                            </div>
                                                            <p class="mt-0.5 text-sm text-gray-500">
                                                                {{ $tracking->created_at ? $tracking->created_at->format('M j, Y \a\t g:i A') : 'N/A' }}
                                                            </p>
                                                        </div>
                                                        <div class="mt-2 text-sm text-gray-700">
                                                            @if($tracking->location)
                                                                <p>{{ $tracking->location }}</p>
                                                            @endif
                                                            @if($tracking->notes)
                                                                <p class="mt-1 text-gray-600">{{ $tracking->notes }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No tracking information</h3>
                                <p class="mt-1 text-sm text-gray-500">Tracking information will appear here once your order is processed.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
