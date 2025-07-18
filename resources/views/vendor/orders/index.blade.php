@extends('layouts.main-view')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-9 text-dashboard-light sm:text-3xl sm:truncate">
                    My Orders
                </h2>
                <p class="mt-1 text-sm text-soft-brown">
                    Track and manage your coffee product orders
                </p>
            </div>
            <div class="mt-4 flex md:mt-0 md:ml-4">
                <a href="{{ route('orders.vendor.create') }}" class="ml-3 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gradient-to-r from-yellow-900 to-yellow-800 hover:from-yellow-800 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-light-brown transition-all duration-200">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Place New Order
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <!-- Total Orders Card -->
            <x-stats-card 
                title="Total Orders" 
                :value="$orderStats['total_orders']"
                iconClass="fa-clipboard-list"
                iconColorClass="text-light-brown"
            />

            <!-- Pending Orders Card -->
            <x-stats-card 
                title="Pending Orders" 
                :value="$orderStats['pending_orders']"
                iconClass="fa-clock"
                iconColorClass="text-light-brown"
            />

            <!-- Delivered Orders Card -->
            <x-stats-card 
                title="Delivered Orders" 
                :value="$orderStats['delivered_orders']"
                iconClass="fa-check-circle"
                iconColorClass="text-light-brown"
            />

            <!-- Total Spent Card -->
            <x-stats-card 
                title="Total Spent" 
                :value="'$' . number_format($orderStats['total_spent'], 0)"
                iconClass="fa-dollar-sign"
                iconColorClass="text-light-brown"
            />
        </div>

        <!-- Orders Table -->
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200">
                <h3 class="text-lg font-semibold text-dashboard-light">Order History</h3>
                <p class="mt-1 text-sm text-soft-brown">Track the status of your coffee product orders</p>
            </div>

            @if($orders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Price</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($orders as $order)
                                <tr class="hover:bg-gray-50 transition-colors duration-200">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">#{{ $order->id }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($order->coffeeProduct)
                                            <div class="text-sm font-medium text-gray-900">{{ $order->coffeeProduct->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $order->coffeeProduct->category ?? 'N/A' }}</div>
                                        @else
                                            <div class="text-sm text-gray-500">Product not available</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ number_format($order->quantity, 0) }} kg</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">${{ number_format($order->total_price, 0) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statuses[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $order->order_date ? $order->order_date->format('M d, Y') : 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('orders.vendor.show', $order) }}" class="text-white bg-light-brown hover:bg-brown p-1  rounded transition-colors duration-200">
                                                <i class="fa-solid fa-eye"></i> View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    {{ $orders->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No orders found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by placing your first order.</p>
                    <div class="mt-6">
                        <a href="{{ route('orders.vendor.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-yellow-900 to-yellow-800 hover:from-yellow-800 hover:to-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition-all duration-200">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Place New Order
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let isPolling = false;

function updateOrderStatus(orders, statuses) {
    orders.forEach(order => {
        // Find the status cell for this order
        const statusCell = document.querySelector(`tr[data-order-id="${order.id}"] .status-badge`);
        if (statusCell) {
            const statusClasses = statuses[order.status] || 'bg-gray-100 text-gray-800';
            statusCell.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-badge ${statusClasses}`;
            statusCell.textContent = order.status.charAt(0).toUpperCase() + order.status.slice(1);
        }
    });
}

function updateStatistics(stats) {
    // Update stats cards values
    const statsElements = {
        'total_orders': document.querySelector('[data-stat="total_orders"]'),
        'pending_orders': document.querySelector('[data-stat="pending_orders"]'), 
        'delivered_orders': document.querySelector('[data-stat="delivered_orders"]'),
        'total_spent': document.querySelector('[data-stat="total_spent"]')
    };

    if (statsElements.total_orders) {
        statsElements.total_orders.textContent = stats.total_orders;
    }
    
    if (statsElements.pending_orders) {
        statsElements.pending_orders.textContent = stats.pending_orders;
    }
    
    if (statsElements.delivered_orders) {
        statsElements.delivered_orders.textContent = stats.completed_orders;
    }
    
    if (statsElements.total_spent) {
        statsElements.total_spent.textContent = '$' + new Intl.NumberFormat('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(stats.total_spent);
    }
}

async function checkForUpdates() {
    if (isPolling) return;
    
    isPolling = true;
    
    try {
        const response = await fetch('/api/vendor/orders/status-updates', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            const data = await response.json();
            
            if (data.success) {
                updateOrderStatus(data.data.orders, data.data.statuses);
                updateStatistics(data.data.stats);
            }
        }
    } catch (error) {
        console.error('Error checking for updates:', error);
    } finally {
        isPolling = false;
    }
}

// Start polling when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add data attributes to status badges and stat values
    document.querySelectorAll('tbody tr').forEach((row, index) => {
        const orderIdCell = row.querySelector('td:first-child .text-sm');
        if (orderIdCell) {
            const orderId = orderIdCell.textContent.replace('#', '').trim();
            row.setAttribute('data-order-id', orderId);
            
            const statusBadge = row.querySelector('td:nth-child(5) span');
            if (statusBadge) {
                statusBadge.classList.add('status-badge');
            }
        }
    });

    // Add data attributes to stats
    const statsCards = document.querySelectorAll('.grid .bg-white');
    statsCards.forEach((card, index) => {
        const valueElement = card.querySelector('.text-2xl, .text-xl');
        if (valueElement) {
            switch(index) {
                case 0:
                    valueElement.setAttribute('data-stat', 'total_orders');
                    break;
                case 1:
                    valueElement.setAttribute('data-stat', 'pending_orders');
                    break;
                case 2:
                    valueElement.setAttribute('data-stat', 'delivered_orders');
                    break;
                case 3:
                    valueElement.setAttribute('data-stat', 'total_spent');
                    break;
            }
        }
    });

    // Start polling every 30 seconds
    setInterval(checkForUpdates, 30000);
    
    // Initial check after 5 seconds
    setTimeout(checkForUpdates, 5000);
});
</script>
@endpush
