@props(['title', 'orders', 'class' => '', 'fullWidth' => false])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between {{ $class }}">
    {{-- Card Header --}}
    <div class="flex justify-between items-center pb-4">
        @isset($title)
            <p class="text-xl pl-2 pb-4 font-bold leading-none text-coffee-brown dark:text-white">{{ $title }}</p>
        @endisset
        
    </div>

    {{-- Orders List --}}
    <div class="space-y-3 grid grid-cols-1 {{ $fullWidth ? 'lg:grid-cols-2' : '' }} gap-4"> 
        @forelse($orders as $order)
            <div class="border rounded-2xl border-pale-brown p-4"> {{-- Container for each order item --}}
                <div class="flex justify-between items-start">
                    {{-- Order Details (Left Side) --}}
                    <div>
                        <p class="text-base  font-semibold text-dashboard-light dark:text-white">{{ $order['name'] ?? 'N/A' }}</p>
                        <p class="text-xs text-soft-brown dark:text-gray-400 mt-2">Order ID: {{ $order['order_id'] ?? 'N/A' }}</p>
                        <p class="text-xs text-dashboard-light dark:text-gray-400 mt-2">{{ $order['productName'] ?? 'N/A' }}</p>
                        <p class="text-xs font-semibold text-dashboard-light dark:text-gray-400 mt-3">Requested Delivery:</p>     
                    </div>
                    {{-- Quantity and Action Buttons (Right Side) --}}
                    <div class="text-right">
                        <div class="flex space-x-2 justify-end">
                            <button class="text-xs font-medium rounded-md p-1 mb-4 bg-off-white text-light-brown ">New Order</button>
                        </div>
                        <p class="text-base mb-5 font-semibold text-gray-900 dark:text-white">{{ $order['quantity'] ?? 'N/A' }} kg</p>
                        <p class="text-xs mb-4 font-medium text-dashboard-light dark:text-white">{{ $order['date'] ?? 'N/A' }}</p>
                        <div class="flex space-x-2 mt-2">
                            <button class="px-4 py-3 text-xs font-medium rounded-md text-dashboard-light bg-off-white hover:bg-light-background dark:text-gray-200 dark:bg-warm-gray dark:hover:bg-mild-gray transition-colors duration-200">Decline</button>
                            <button class="px-4 py-3 text-xs font-medium rounded-md text-white bg-light-brown hover:bg-brown transition-colors duration-200">Accept Order</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 dark:text-gray-400">No pending orders.</p>
        @endforelse
    </div>
</div>