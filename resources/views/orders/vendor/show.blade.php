@extends('layouts.main-view')

@section('sidebar')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold leading-7 text-yellow-900 sm:text-3xl">
                        Order Details
                    </h2>
                    <p class="mt-1 text-sm text-yellow-600">
                        View and track your order #{{ $order->id }}
                    </p>
                </div>
                <a href="{{ route('orders.vendor.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
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
                        <h3 class="text-lg font-semibold text-gray-900">Order Information</h3>
                    </div>

                    <div class="p-6 space-y-6">
                        <!-- Order Status -->
                        <div class="flex items-center justify-between">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Order Status</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </dd>
                            </div>
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
                            @endif
                        </div>

                        <!-- Product Details -->
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Product</dt>
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
                                <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ number_format($order->quantity, 2) }} kg</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Price</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-900">${{ number_format($order->total_price, 2) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Order Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $order->order_date ? $order->order_date->format('F j, Y') : 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Order ID</dt>
                                <dd class="mt-1 text-sm text-gray-900">#{{ $order->id }}</dd>
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($order->notes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
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
                        <h3 class="text-lg font-semibold text-gray-900">Order Tracking</h3>
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
                                                                {{ $tracking->created_at->format('M j, Y \a\t g:i A') }}
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
