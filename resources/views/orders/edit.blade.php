@extends('layouts.main-view')

@section('sidebar')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-yellow-900 sm:text-3xl sm:truncate">
                    Edit Order #{{ $order->id }}
                </h2>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('orders.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-900 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Back to Orders
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Edit Order Form -->
            <div class="lg:col-span-2">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-yellow-900 mb-4">Order Details</h3>
                        
                        <!-- Error Messages -->
                        @if ($errors->any())
                            <div class="mb-4 bg-red-50 border border-red-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Please fix the following errors:</h3>
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
                            <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
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
                        
                        <form action="{{ route('orders.update', $order) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Order Date -->
                            <div class="mb-4">
                                <label for="order_date" class="block text-sm font-medium text-yellow-900">Order Date</label>
                                <input type="date" id="order_date" name="order_date" value="{{ old('order_date', $order->order_date ? $order->order_date->format('Y-m-d') : '') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('order_date') border-red-300 @enderror" required>
                                @error('order_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Total Amount -->
                            <div class="mb-4">
                                <label for="total_amount" class="block text-sm font-medium text-yellow-900">Total Amount (whole numbers only)</label>
                                <input type="number" step="1" min="0" id="total_amount" name="total_amount" value="{{ old('total_amount', $order->total_amount) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('total_amount') border-red-300 @enderror" required>
                                @error('total_amount')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Quantity -->
                            <div class="mb-4">
                                <label for="quantity" class="block text-sm font-medium text-yellow-900">Quantity (kg)</label>
                                <input type="number" name="quantity" id="quantity" min="1" step="1" value="{{ old('quantity', $order->quantity) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('quantity') border-red-300 @enderror" required>
                                @error('quantity')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="mb-4">
                                <label for="status" class="block text-sm font-medium text-yellow-900">Status</label>
                                <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('status') border-red-300 @enderror" required>
                                    <option value="pending" {{ old('status', $order->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="confirmed" {{ old('status', $order->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                    <option value="shipped" {{ old('status', $order->status) == 'shipped' ? 'selected' : '' }}>Shipped</option>
                                    <option value="delivered" {{ old('status', $order->status) == 'delivered' ? 'selected' : '' }}>Delivered</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-yellow-900">Notes</label>
                                <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('notes') border-red-300 @enderror">{{ old('notes', $order->notes) }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-6">
                                <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-900 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    Update Order
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Information & Delete -->
            <div class="lg:col-span-1">
                <!-- Order Summary -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-yellow-900 mb-4">Order Summary</h3>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-yellow-600">Order ID</dt>
                                <dd class="text-sm text-yellow-900">#{{ $order->id }}</dd>
                            </div>
                            @if($order->supplier)
                            <div>
                                <dt class="text-sm font-medium text-yellow-600">Supplier</dt>
                                <dd class="text-sm text-yellow-900">{{ $order->supplier->name }}</dd>
                            </div>
                            @endif
                            @if($order->wholesaler)
                            <div>
                                <dt class="text-sm font-medium text-yellow-600">Wholesaler</dt>
                                <dd class="text-sm text-yellow-900">{{ $order->wholesaler->name }}</dd>
                            </div>
                            @endif
                            @if($order->rawCoffee)
                            <div>
                                <dt class="text-sm font-medium text-yellow-600">Coffee</dt>
                                <dd class="text-sm text-yellow-900">{{ $order->rawCoffee->grade }} - {{ $order->rawCoffee->coffee_type }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-yellow-600">Created</dt>
                                <dd class="text-sm text-yellow-900">{{ $order->created_at->format('M d, Y g:i A') }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Delete Order -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-red-900 mb-4">Danger Zone</h3>
                        <p class="text-sm text-red-600 mb-4">Once you delete this order, there is no going back. Please be certain.</p>
                        <form action="{{ route('orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this order? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                Delete Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 