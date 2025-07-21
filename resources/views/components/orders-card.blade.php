@props(['title', 'orders', 'class' => '', 'fullWidth' => false])

<div class="bg-white p-6 w-full rounded-2xl shadow-md flex flex-col justify-between {{ $class }}">
    {{-- Card Header --}}
    <div class="flex justify-between items-center pb-4">
        @isset($title)
            <h1 class="text-xl pl-2 pb-4 font-bold leading-none text-coffee-brown dark:text-white">{{ $title }}</h1>
        @endisset
        
    </div>

    {{-- Orders List --}}
    <div class="space-y-3 grid grid-cols-1 {{ $fullWidth ? 'lg:grid-cols-2' : '' }} gap-4"> 
        @forelse($orders as $order)
            <div class="border rounded-2xl border-pale-brown p-4"> {{-- Container for each order item --}}
                <div class="flex justify-between items-start">
                    {{-- Order Details (Left Side) --}}
                    <div class="pb-10">
                        <p class="text-base  font-semibold text-dashboard-light dark:text-white">{{ $order['name'] ?? 'N/A' }}</p>
                        <p class="text-xs text-soft-brown dark:text-gray-400 mt-2">Order ID: {{ $order['order_id'] ?? 'N/A' }}</p>
                        <p class="text-xs text-dashboard-light dark:text-gray-400 mt-2">{{ $order['productName'] ?? 'N/A' }}</p>
                        <p class="text-xs font-semibold text-dashboard-light dark:text-gray-400 mt-3">Requested Delivery:</p>     
                    </div>
                    {{-- Quantity and Action Buttons (Right Side) --}}
                    <div class="text-right">
                        <p class="text-base mb-5 font-semibold text-gray-900 dark:text-white">{{ $order['quantity'] ?? 'N/A' }} kg</p>
                        <p class="text-xs pb-2 pt-9 font-medium text-dashboard-light dark:text-white">{{ $order['date'] ?? 'N/A' }}</p>
                        <div class="flex space-x-2 mt-2">
                            @if(($order['status'] ?? 'pending') === 'pending' && isset($order['order_id']) && $order['order_id'] !== 'N/A')
                                @if(($order['order_type'] ?? 'received') === 'received')
                                    {{-- Orders received by the user - show Accept and Reject buttons --}}
                                    @if(Auth::user()->isSupplier())
                                        {{-- Supplier-specific routes --}}
                                        <form method="POST" action="{{ route('orders.supplier.reject', $order['order_id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="px-4 py-3 text-xs font-medium rounded-md text-dashboard-light bg-off-white hover:bg-light-background dark:text-gray-200 dark:bg-warm-gray dark:hover:bg-mild-gray transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    onclick="return confirm('Are you sure you want to reject this order?')"
                                                    onsubmit="this.disabled=true; this.textContent='Processing...'">
                                                Reject
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('orders.supplier.accept', $order['order_id']) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="px-4 py-3 text-xs font-medium rounded-md text-white bg-light-brown hover:bg-brown transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    onclick="return confirm('Are you sure you want to accept this order?')"
                                                    onsubmit="this.disabled=true; this.textContent='Processing...'">
                                                Accept Order
                                            </button>
                                        </form>
                                    @else
                                        {{-- Generic routes for other user types --}}
                                        <form method="POST" action="{{ route('orders.update-status', $order['order_id']) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <input type="hidden" name="notes" value="Order rejected from dashboard">
                                            <button type="submit" 
                                                    class="px-4 py-3 text-xs font-medium rounded-md text-dashboard-light bg-off-white hover:bg-light-background dark:text-gray-200 dark:bg-warm-gray dark:hover:bg-mild-gray transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    onclick="return confirm('Are you sure you want to reject this order?')"
                                                    onsubmit="this.disabled=true; this.textContent='Processing...'">
                                                Reject
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('orders.update-status', $order['order_id']) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <input type="hidden" name="notes" value="Order confirmed from dashboard">
                                            <button type="submit" 
                                                    class="px-4 py-3 text-xs font-medium rounded-md text-white bg-light-brown hover:bg-brown transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    onclick="return confirm('Are you sure you want to accept this order?')"
                                                    onsubmit="this.disabled=true; this.textContent='Processing...'">
                                                Accept Order
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    {{-- Orders made by the user - show Cancel button --}}
                                    <form method="POST" action="{{ route('orders.update-status', $order['order_id']) }}" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <input type="hidden" name="notes" value="Order cancelled by user from dashboard">
                                        <button type="submit" 
                                                class="px-4 py-3 text-xs font-medium rounded-md text-white bg-red-500 hover:bg-red-600 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                                onclick="return confirm('Are you sure you want to cancel this order?')"
                                                onsubmit="this.disabled=true; this.textContent='Processing...'">
                                            Cancel Order
                                        </button>
                                    </form>
                                @endif
                            @else
                                <div class="flex space-x-2">
                                    <span class="px-4 py-3 text-xs font-medium rounded-md text-dashboard-light bg-gray-100 dark:text-gray-400 dark:bg-warm-gray">
                                        {{ ucfirst($order['status'] ?? 'Unknown') }}
                                    </span>
                                    @if(isset($order['order_id']) && $order['order_id'] !== 'N/A')
                                        <a href="{{ route('orders.show', $order['order_id']) }}" 
                                           class="px-4 py-3 text-xs font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 transition-colors duration-200">
                                            View Details
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8">
                <div class="mx-auto h-12 w-12 text-gray-400 mb-4">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <p class="text-gray-500 dark:text-gray-400 text-sm">No pending orders found.</p>
                @if(Auth::user()->isVendor())
                    <p class="text-gray-400 dark:text-gray-500 text-xs mt-2">Place your first order to see it here.</p>
                @endif
            </div>
        @endforelse
    </div>
</div>