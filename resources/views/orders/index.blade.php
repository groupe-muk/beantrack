@extends('layouts.main-view')

@section('sidebar')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-yellow-900 sm:text-3xl sm:truncate">
                    Order Management
                </h2>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('orders.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-900 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                    Create New Order
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8" id="order-stats">
            <!-- Stats will be loaded via JavaScript -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-800 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-yellow-600 truncate">
                                    Orders Placed to Suppliers
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-yellow-900">
                                        {{ $ordersPlaced->total() }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-800 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-yellow-600 truncate">
                                    Orders Received from Wholesalers
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-yellow-900">
                                        {{ $ordersReceived->total() }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Placed to Suppliers -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-yellow-900">
                    Orders Placed to Suppliers
                </h3>
            </div>
            <div class="border-t border-amber-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-100">
                        <thead class="bg-amber-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Supplier
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Coffee
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-amber-100">
                            @forelse ($ordersPlaced as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-900">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->supplier->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->rawCoffee->grade }} - {{ $order->rawCoffee->coffee_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->total_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = $statuses[$order->status] ?? 'bg-amber-100 text-yellow-800';
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->order_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('orders.show', $order) }}" class="text-yellow-700 hover:text-yellow-900 mr-3">View</a>
                                        <button onclick="updateStatus({{ $order->id }})" class="text-yellow-700 hover:text-yellow-900">Update Status</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 text-center">
                                        No orders placed to suppliers
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Orders Received from Wholesalers -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-yellow-900">
                    Orders Received from Wholesalers
                </h3>
            </div>
            <div class="border-t border-amber-100">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-amber-100">
                        <thead class="bg-amber-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Order ID
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Wholesaler
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Coffee
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Date
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-600 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-amber-100">
                            @forelse ($ordersReceived as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-yellow-900">
                                        #{{ $order->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->wholesaler->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->rawCoffee->grade }} - {{ $order->rawCoffee->coffee_type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->total_amount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $statusClass = $statuses[$order->status] ?? 'bg-amber-100 text-yellow-800';
                                        @endphp
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600">
                                        {{ $order->order_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('orders.show', $order) }}" class="text-yellow-700 hover:text-yellow-900 mr-3">View</a>
                                        <button onclick="updateStatus({{ $order->id }})" class="text-yellow-700 hover:text-yellow-900">Update Status</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-yellow-600 text-center">
                                        No orders received from wholesalers
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-amber-100 sm:px-6">
            <div class="flex-1">
                {{ $ordersPlaced->links() }}
            </div>
            <div class="flex-1">
                {{ $ordersReceived->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal code remains unchanged except for color adjustments as above -->

@push('scripts')
<script>
    function updateStatus(orderId) {
        const form = document.getElementById('statusForm');
        form.action = `/orders/${orderId}/status`;
        document.getElementById('statusModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('statusModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
