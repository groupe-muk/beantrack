@extends('layouts.main-view')

@section('content')

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-4">
            <div >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-dashboard-light mb-2">Received Orders</h1>
                        <p class="text-soft-brown">Manage orders received from the Factory</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stats-card
                title="Total Orders"
                value="{{ $orderStats['total_orders'] ?? 0 }}"
                iconClass="fa-cube"
                iconColorClass="text-light-brown"
                changeText="All time orders"
                changeType="positive"
                changeIconClass="fa-arrow-up"
            />

            <x-stats-card
                title="Pending"
                value="{{ $orderStats['pending_orders'] ?? 0 }}"
                iconClass="fa-clock"
                iconColorClass="text-light-brown"
                changeText="Awaiting response"
                changeIconClass="fa-hourglass-half"
            />

            <x-stats-card
                title="Shipped"
                value="{{ $orderStats['shipped_orders'] ?? 0 }}"
                iconClass="fa-shipping-fast"
                iconColorClass="text-light-brown"
                changeText="Orders in Transit"
                changeIconClass="fa-truck"
            />

            <x-stats-card
                title="Total Revenue"
                value="${{ number_format($orderStats['total_revenue'] ?? 0, 0) }}"
                iconClass="fa-dollar-sign"
                iconColorClass="text-light-brown"
                changeText="All time earnings"
                changeType="positive"
                changeIconClass="fa-chart-line"
            />
        </div>

        <!-- Orders Table -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">Orders</h2>
            </div>
            
            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coffee Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity (kgs)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price ($) </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($orders as $order)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $order->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->rawCoffee->coffee_type ?? 'N/A' }}</div>
                                        <div class="text-sm text-gray-500">Grade: {{ $order->rawCoffee->grade ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($order->quantity) }} 
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($order->rawCoffee)
                                            @php
                                                $supplier = \App\Models\Supplier::where('user_id', auth()->id())->first();
                                                $availableStock = $supplier ? 
                                                    \App\Models\Inventory::getAvailableStockForSupplier($order->raw_coffee_id, $supplier->id) : 
                                                    \App\Models\Inventory::getAvailableStock($order->raw_coffee_id);
                                                $isStockSufficient = $availableStock >= $order->quantity;
                                            @endphp
                                            <div class="{{ $isStockSufficient ? 'text-green-600' : 'text-red-600' }}">
                                                {{ number_format($availableStock) }} kg
                                            </div>
                                            @if(!$isStockSufficient && $order->status === 'pending')
                                                <div class="text-xs text-red-500">
                                                    Short: {{ number_format($order->quantity - $availableStock) }} kg
                                                </div>
                                            @elseif($isStockSufficient && $order->status === 'pending')
                                                <div class="text-xs text-green-500">✓ Available</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($order->total_price, 0) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('orders.supplier.show', $order) }}" 
                                               class="bg-light-brown text-white hover:bg-brown p-1 rounded transition-colors duration-200">
                                                <i class="fa-solid fa-eye"></i> View
                                            </a>                                                
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders</h3>
                    <p class="mt-1 text-sm text-gray-500">You haven't received any orders yet.</p>
                </div>
            @endif
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
