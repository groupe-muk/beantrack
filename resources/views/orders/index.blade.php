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
                <p class="mt-1 text-sm text-yellow-600">
                    Manage orders placed to suppliers and received from wholesalers
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('orders.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-yellow-800 to-yellow-900 hover:from-yellow-700 hover:to-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Create New Order
                </a>
            </div>
        </div>

        <!-- Enhanced Stats Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Orders Placed -->
            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 overflow-hidden shadow-lg rounded-xl border border-yellow-100 hover:shadow-xl transition-all duration-300">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-yellow-800 to-yellow-900 rounded-lg p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-yellow-700 truncate">
                                    Orders Placed
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-yellow-900">
                                        {{ $ordersPlaced->total() }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-yellow-600">
                                        to suppliers
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Orders Received -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 overflow-hidden shadow-lg rounded-xl border border-blue-100 hover:shadow-xl transition-all duration-300">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-blue-700 truncate">
                                    Orders Received
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-blue-900">
                                        {{ $ordersReceived->total() }}
                                    </div>
                                    <div class="ml-2 flex items-baseline text-sm font-semibold text-blue-600">
                                        from wholesalers
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Value Placed -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 overflow-hidden shadow-lg rounded-xl border border-green-100 hover:shadow-xl transition-all duration-300">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-green-700 truncate">
                                    Total Value Placed
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-green-900">
                                        ${{ number_format($ordersPlaced->sum('total_amount'), 2) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Value Received -->
            <div class="bg-gradient-to-br from-purple-50 to-violet-50 overflow-hidden shadow-lg rounded-xl border border-purple-100 hover:shadow-xl transition-all duration-300">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg p-3 shadow-lg">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-purple-700 truncate">
                                    Total Value Received
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-purple-900">
                                        ${{ number_format($ordersReceived->sum('total_amount'), 2) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Interface -->
        <div class="bg-white shadow-xl rounded-xl overflow-hidden">
            <!-- Tab Navigation -->
            <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-b border-amber-100">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button type="button" 
                            onclick="switchTab('placed')" 
                            id="tab-placed"
                            class="tab-button border-transparent text-yellow-600 hover:text-yellow-800 hover:border-yellow-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span>Orders Placed</span>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $ordersPlaced->total() }}</span>
                    </button>
                    <button type="button" 
                            onclick="switchTab('received')" 
                            id="tab-received"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200 flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span>Orders Received</span>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $ordersReceived->total() }}</span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Orders Placed -->
            <div id="content-placed" class="tab-content">
                <div class="px-6 py-4 bg-gradient-to-r from-yellow-50 to-amber-50 border-b border-amber-100">
                    <h3 class="text-lg leading-6 font-semibold text-yellow-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Orders Placed to Suppliers
                    </h3>
                    <p class="mt-1 text-sm text-yellow-600">Orders you have placed with coffee suppliers</p>
                </div>
                
                @if($ordersPlaced->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-amber-100">
                            <thead class="bg-amber-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">
                                        Order Details
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">
                                        Supplier
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">
                                        Coffee Product
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">
                                        Amount & Quantity
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-yellow-700 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-amber-50">
                                @foreach ($ordersPlaced as $order)
                                    <tr class="hover:bg-yellow-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-yellow-900">#{{ $order->id }}</div>
                                                <div class="text-sm text-yellow-600">{{ $order->order_date->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->supplier->name }}</div>
                                            <div class="text-sm text-gray-500">Supplier</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->rawCoffee->grade }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->rawCoffee->coffee_type }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->quantity }}kg</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClass = $statuses[$order->status] ?? 'bg-amber-100 text-yellow-800';
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 hover:text-yellow-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-150">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for Placed Orders -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-amber-100 sm:px-6">
                        {{ $ordersPlaced->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No orders placed</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by placing your first order with a supplier.</p>
                        <div class="mt-6">
                            <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Place Your First Order
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Tab Content: Orders Received -->
            <div id="content-received" class="tab-content hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100">
                    <h3 class="text-lg leading-6 font-semibold text-blue-900 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Orders Received from Wholesalers
                    </h3>
                    <p class="mt-1 text-sm text-blue-600">Orders received from wholesaler partners</p>
                </div>
                
                @if($ordersReceived->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-100">
                            <thead class="bg-blue-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Order Details
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Wholesaler
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Coffee Product
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Amount & Quantity
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-blue-700 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-blue-50">
                                @foreach ($ordersReceived as $order)
                                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-blue-900">#{{ $order->id }}</div>
                                                <div class="text-sm text-blue-600">{{ $order->order_date->format('M d, Y') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->wholesaler->name }}</div>
                                            <div class="text-sm text-gray-500">Wholesaler</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $order->rawCoffee->grade }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->rawCoffee->coffee_type }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_amount, 2) }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->quantity }}kg</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusClass = $statuses[$order->status] ?? 'bg-blue-100 text-blue-800';
                                            @endphp
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('orders.edit', $order) }}" class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-150">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination for Received Orders -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-blue-100 sm:px-6">
                        {{ $ordersReceived->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No orders received</h3>
                        <p class="mt-1 text-sm text-gray-500">You haven't received any orders from wholesalers yet.</p>
                        <div class="mt-6">
                            <p class="text-sm text-gray-500">Orders from wholesalers will appear here automatically when received.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
function switchTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active state from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-yellow-500', 'text-yellow-600', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active state to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    
    if (tabName === 'placed') {
        activeTab.classList.add('border-yellow-500', 'text-yellow-600');
    } else {
        activeTab.classList.add('border-blue-500', 'text-blue-600');
    }
}

// Initialize with the first tab active
document.addEventListener('DOMContentLoaded', function() {
    switchTab('placed');
});
</script>
@endsection
