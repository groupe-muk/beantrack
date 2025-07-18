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
                    Manage your orders placed to suppliers and received from wholesalers
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('orders.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-yellow-900 to-yellow-800 hover:from-yellow-800 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Create New Order
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Orders Placed Card -->
            <div class="bg-gradient-to-br from-yellow-50 to-amber-50 overflow-hidden shadow-lg rounded-xl border border-yellow-100 hover:shadow-xl transition-shadow duration-300">
                <div class="px-6 py-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-yellow-900 to-yellow-800 rounded-lg p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
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
                                    <div class="ml-2 text-sm text-yellow-600">
                                        to suppliers
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Received Card -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 overflow-hidden shadow-lg rounded-xl border border-green-100 hover:shadow-xl transition-shadow duration-300">
                <div class="px-6 py-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-green-600 to-green-700 rounded-lg p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-green-700 truncate">
                                    Orders Received
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-green-900">
                                        {{ $ordersReceived->total() }}
                                    </div>
                                    <div class="ml-2 text-sm text-green-600">
                                        from vendors
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Value Card -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 overflow-hidden shadow-lg rounded-xl border border-blue-100 hover:shadow-xl transition-shadow duration-300">
                <div class="px-6 py-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-blue-700 truncate">
                                    Total Value
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-blue-900">
                                        ${{ number_format($ordersPlaced->sum('total_price') + $ordersReceived->sum('total_price'), 0) }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Orders Card -->
            <div class="bg-gradient-to-br from-purple-50 to-indigo-50 overflow-hidden shadow-lg rounded-xl border border-purple-100 hover:shadow-xl transition-shadow duration-300">
                <div class="px-6 py-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-gradient-to-r from-purple-600 to-purple-700 rounded-lg p-3">
                            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-purple-700 truncate">
                                    Pending Orders
                                </dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-bold text-purple-900">
                                        {{ $ordersPlaced->where('status', 'pending')->count() + $ordersReceived->where('status', 'pending')->count() }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabbed Interface -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
                <nav class="flex space-x-8 px-6" aria-label="Tabs">
                    <button type="button" 
                            onclick="switchTab('placed')" 
                            id="tab-placed"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200">
                        <div class="flex items-center">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Orders Placed
                            <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $ordersPlaced->total() }}</span>
                        </div>
                    </button>
                    <button type="button" 
                            onclick="switchTab('received')" 
                            id="tab-received"
                            class="tab-button border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-200">
                        <div class="flex items-center">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            Orders Received
                            <span class="ml-2 bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $ordersReceived->total() }}</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Orders Placed Tab -->
                <div id="content-placed" class="tab-content">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Orders Placed to Suppliers</h3>
                        <p class="text-sm text-gray-600">Track and manage orders you've placed with your suppliers</p>
                    </div>
                    
                    @if($ordersPlaced->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coffee Details</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($ordersPlaced as $order)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                            </td>                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->supplier)
                                    <div class="text-sm font-medium text-gray-900">{{ $order->supplier->name }}</div>
                                @else
                                    <div class="text-sm font-medium text-gray-900">No supplier specified</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->rawCoffee)
                                    <div class="text-sm text-gray-900">{{ $order->rawCoffee->coffee_type }}</div>
                                    <div class="text-sm text-gray-500">Grade: {{ $order->rawCoffee->grade }}</div>
                                @else
                                    <div class="text-sm text-gray-900">No coffee specified</div>
                                    <div class="text-sm text-gray-500">-</div>
                                @endif
                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($order->quantity) }} kg</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_price, 0) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                        'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                        'delivered' => 'bg-green-100 text-green-800 border-green-200'
                                                    ];
                                                    $statusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($order->order_date && $order->order_date instanceof \Carbon\Carbon)
                                                    <div class="text-sm text-gray-900">{{ $order->order_date->format('M d, Y') }}</div>
                                                    <div class="text-sm text-gray-500">{{ $order->order_date->diffForHumans() }}</div>
                                                @else
                                                    <div class="text-sm text-gray-900">No date set</div>
                                                    <div class="text-sm text-gray-500">-</div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('orders.edit', $order) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-colors duration-200">
                                                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for Orders Placed -->
                        <div class="mt-6">
                            {{ $ordersPlaced->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders placed</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by placing your first order to a supplier.</p>
                            <div class="mt-6">
                                <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-yellow-900 to-yellow-800 hover:from-yellow-800 hover:to-yellow-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Create Order
                                </a>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Orders Received Tab -->
                <div id="content-received" class="tab-content hidden">
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Orders Received from Vendors</h3>
                        <p class="text-sm text-gray-600">Track and manage orders received from your vendor customers</p>
                    </div>
                    
                    @if($ordersReceived->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coffee Details</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($ordersReceived as $order)
                                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                            </td>                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->wholesaler)
                                    <div class="text-sm font-medium text-gray-900">{{ $order->wholesaler->name }}</div>
                                @else
                                    <div class="text-sm font-medium text-gray-900">No vendor specified</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->rawCoffee)
                                    <div class="text-sm text-gray-900">{{ $order->rawCoffee->coffee_type }}</div>
                                    <div class="text-sm text-gray-500">Grade: {{ $order->rawCoffee->grade }}</div>
                                @else
                                    <div class="text-sm text-gray-900">No coffee specified</div>
                                    <div class="text-sm text-gray-500">-</div>
                                @endif
                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ number_format($order->quantity) }} kg</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_price, 0) }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $statusClasses = [
                                                        'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                                        'confirmed' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                        'shipped' => 'bg-purple-100 text-purple-800 border-purple-200',
                                                        'delivered' => 'bg-green-100 text-green-800 border-green-200'
                                                    ];
                                                    $statusClass = $statusClasses[$order->status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
                                                @endphp
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusClass }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->order_date && $order->order_date instanceof \Carbon\Carbon)
                                    <div class="text-sm text-gray-900">{{ $order->order_date->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $order->order_date->diffForHumans() }}</div>
                                @else
                                    <div class="text-sm text-gray-900">No date set</div>
                                    <div class="text-sm text-gray-500">-</div>
                                @endif
                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a href="{{ route('orders.edit', $order) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                                    <svg class="mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination for Orders Received -->
                        <div class="mt-6">
                            {{ $ordersReceived->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No orders received</h3>
                            <p class="mt-1 text-sm text-gray-500">Orders from wholesalers will appear here when they place orders.</p>
                        </div>
                    @endif
                </div>
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
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(tab => {
        tab.classList.remove('border-yellow-500', 'text-yellow-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-yellow-500', 'text-yellow-600');
    
    // Store active tab in localStorage
    localStorage.setItem('activeOrderTab', tabName);
}

// Initialize tabs on page load
document.addEventListener('DOMContentLoaded', function() {
    // Get saved tab from localStorage or default to 'placed'
    const activeTab = localStorage.getItem('activeOrderTab') || 'placed';
    switchTab(activeTab);
});
</script>

@endsection
