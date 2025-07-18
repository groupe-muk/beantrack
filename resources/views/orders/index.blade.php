@extends('layouts.main-view')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-dashboard-light mb-2">Order Management</h1>
                <p class="text-soft-brown">Manage orders from vendors and to suppliers</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('orders.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-light-brown hover:bg-brown text-white text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Place Order to Supplier
                </a>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card
            title="Total Orders Placed"
            value="{{ $ordersPlaced->total() }}"
            iconClass="fa-shopping-cart"
            iconColorClass="text-light-brown"
            changeText="To suppliers"
            changeType="neutral"

        />

        <x-stats-card
            title="Orders Received"
            value="{{ $ordersReceived->total() }}"
            iconClass="fa-inbox"
            iconColorClass="text-light-brown"
            changeText="From vendors"
            changeType="neutral"

        />

        <x-stats-card
            title="Pending Review"
            value="{{ $ordersReceived->where('status', 'pending')->count() }}"
            iconClass="fa-clock"
            iconColorClass="text-light-brown"
            changeText="Awaiting action"
            changeIconClass="fa-hourglass-half"
        />

        <x-stats-card
            title="Total Incoming Revenue"
            value="${{ number_format($ordersReceived->sum('total_price'), 0) }}"
            iconClass="fa-dollar-sign"
            iconColorClass="text-light-brown"
            changeText="All incoming orders"
            changeType="positive"
            changeIconClass="fa-chart-line"
        />
    </div>

    <!-- Tabs Navigation -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('received')" id="tab-received" 
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-light-brown text-light-brown">
                    Orders from Vendors
                </button>
                <button onclick="showTab('placed')" id="tab-placed" 
                        class="tab-button py-2 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Orders to Suppliers
                </button>
            </nav>
        </div>
    </div>

    <!-- Orders from Vendors Tab -->
    <div id="content-received" class="tab-content">
        <div class="mb-4">
            <h3 class="text-xl font-semibold text-dashboard-light mb-2">Orders Received from Vendors</h3>
            <p class="text-soft-brown">Review and manage orders received from vendor customers</p>
        </div>
        
        @if($ordersReceived->count() > 0)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-dashboard-light">Vendor Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vendor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Coffee Product</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ordersReceived as $order)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($order->wholesaler)
                                            <div class="text-sm font-medium text-gray-900">{{ $order->wholesaler->name }}</div>
                                        @else
                                            <div class="text-sm font-medium text-gray-900">No vendor specified</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($order->coffeeProduct)
                                            <div class="text-sm text-gray-900">{{ $order->coffeeProduct->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->coffeeProduct->category }}</div>
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
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order->status ? ucfirst($order->status) : 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('orders.show', $order) }}" 
                                           class="bg-light-brown text-white hover:bg-brown px-3 py-1 rounded transition-colors duration-200">
                                            <i class="fa-solid fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for Orders from Vendors -->
                @if($ordersReceived->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $ordersReceived->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders received</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't received any orders from vendors yet.</p>
            </div>
        @endif
    </div>

    <!-- Orders to Suppliers Tab -->
    <div id="content-placed" class="tab-content hidden">
        <div class="mb-4">
            <h3 class="text-xl font-semibold text-dashboard-light mb-2">Orders Placed to Suppliers</h3>
            <p class="text-soft-brown">Track and manage orders you've placed with suppliers</p>
        </div>
        
        @if($ordersPlaced->count() > 0)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-dashboard-light">Supplier Orders</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raw Coffee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($ordersPlaced as $order)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
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
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ $order->status ? ucfirst($order->status) : 'Unknown' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $order->created_at ? $order->created_at->format('M d, Y') : 'N/A' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('orders.show', $order) }}" 
                                           class="bg-light-brown text-white hover:bg-brown px-3 py-1 rounded transition-colors duration-200">
                                            <i class="fa-solid fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination for Orders Placed -->
                @if($ordersPlaced->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $ordersPlaced->links() }}
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No orders placed</h3>
                <p class="mt-1 text-sm text-gray-500">You haven't placed any orders to suppliers yet.</p>
                <div class="mt-6">
                    <a href="{{ route('orders.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-light-brown hover:bg-brown">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        Create Order
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Success/Error Messages -->
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
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active styles from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('border-light-brown', 'text-light-brown');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active styles to selected tab
    const activeTab = document.getElementById('tab-' + tabName);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-light-brown', 'text-light-brown');
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const successAlert = document.getElementById('success-alert');
    const errorAlert = document.getElementById('error-alert');
    if (successAlert) successAlert.style.display = 'none';
    if (errorAlert) errorAlert.style.display = 'none';
}, 5000);

// Initialize tabs on page load
document.addEventListener('DOMContentLoaded', function() {
    // Default to showing the received orders tab
    showTab('received');
});
</script>
@endsection