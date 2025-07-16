<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request as LaravelRequest;
use App\Models\OrderTracking;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // Add this for unique validation if needed
use Log;

class OrderController extends Controller
{
    /**
     * Display the order management dashboard
     */
    public function index()
    {
        // Get orders placed to suppliers (with supplier_id set)
        $ordersPlaced = Order::with(['supplier', 'rawCoffee', 'orderTrackings'])
            ->whereNotNull('supplier_id')
            ->latest()
            ->paginate(10);

        // Get orders received from wholesalers (with wholesaler_id set)
        $ordersReceived = Order::with(['wholesaler', 'rawCoffee', 'orderTrackings'])
            ->whereNotNull('wholesaler_id')
            ->latest()
            ->paginate(10);

        $statuses = $this->getStatuses();

        return view('orders.index', compact('ordersPlaced', 'ordersReceived', 'statuses'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $suppliers = Supplier::pluck('name', 'id');
        $wholesalers = Wholesaler::pluck('name', 'id');
        $grades = RawCoffee::distinct()->pluck('grade');
        $coffeeTypes = RawCoffee::distinct()->pluck('coffee_type');
        $rawCoffees = RawCoffee::get();
        $coffeeProducts = CoffeeProduct::pluck('name', 'id');

        return view('orders.create', compact('suppliers', 'wholesalers', 'grades', 'coffeeTypes', 'rawCoffees', 'coffeeProducts'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(LaravelRequest $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:supplier,id',
            'grade' => 'required|string',
            'coffee_type' => 'required|string',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:1', // Add quantity validation
            'notes' => 'nullable|string|max:500',
        ]);

        // Find the raw coffee that matches both grade and coffee_type
        $rawCoffee = RawCoffee::where('grade', $validated['grade'])
            ->where('coffee_type', $validated['coffee_type'])
            ->first();

        if (!$rawCoffee) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['grade' => 'No raw coffee found matching the selected grade and coffee type.']);
        }

        $order = Order::create([
            'supplier_id' => $validated['supplier_id'],
            'raw_coffee_id' => $rawCoffee->id,
            'order_date' => $validated['order_date'],
            'total_amount' => $validated['total_amount'],
            'quantity' => $validated['quantity'], // Use the actual quantity from form
            'total_price' => $validated['total_amount'], // Map total_amount to total_price
            'status' => 'pending', // Default status
            'notes' => $validated['notes'] ?? null
        ]);

        // Refresh the order model to get the auto-generated ID from the database trigger
        $order->refresh();

        // Create an initial tracking record only for shipped orders
        // Since this is a new order with 'pending' status, we'll skip tracking for now
        // Tracking can be added later when the order status changes to 'shipped'

        return redirect()->route('orders.create')->with('success', 'Order created successfully!');
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['supplier', 'wholesaler', 'rawCoffee', 'coffeeProduct', 'orderTrackings']);
        return view('orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order.
     */
    public function edit(Order $order)
    {
        return view('orders.edit', compact('order'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(LaravelRequest $request, Order $order)
    {
        $validated = $request->validate([
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:1',
            'status' => ['required', Rule::in(['pending', 'confirmed', 'shipped', 'delivered'])],
            'notes' => 'nullable|string|max:500',
        ]);

        // Check if status is changing
        $statusChanged = $order->status !== $validated['status'];
        $oldStatus = $order->status;

        // Update the order
        $order->update($validated);

        // If status changed, create tracking record
        if ($statusChanged) {
            $trackableStatuses = ['shipped', 'in-transit', 'delivered'];
            if (in_array($validated['status'], $trackableStatuses)) {
                // Map order status to tracking status
                $trackingStatus = $validated['status']; // 'shipped' and 'delivered' are the same
                if ($validated['status'] === 'confirmed') {
                    $trackingStatus = 'shipped'; // Map confirmed to shipped for tracking
                }
                
                // Create a new tracking record
                $order->orderTrackings()->create([
                    'status' => $trackingStatus,
                    'location' => Auth::check() ? ('Updated by ' . Auth::user()->name) : 'System Update',
                    'notes' => 'Status updated from ' . $oldStatus . ' to ' . $validated['status']
                ]);
            }
        }

        return redirect()->route('orders.edit', $order)->with('success', 'Order updated successfully.');
    }

    /**
     * Update the order status
     */
    public function updateStatus(LaravelRequest $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'confirmed', 'shipped', 'delivered'])], // Match database enum
            'notes' => 'nullable|string|max:500',
        ]);

        // Only create tracking records for trackable statuses
        $trackableStatuses = ['shipped', 'in-transit', 'delivered'];
        if (in_array($validated['status'], $trackableStatuses)) {
            // Map order status to tracking status
            $trackingStatus = $validated['status']; // 'shipped' and 'delivered' are the same
            if ($validated['status'] === 'confirmed') {
                $trackingStatus = 'shipped'; // Map confirmed to shipped for tracking
            }
            
            // Create a new tracking record
            $order->orderTrackings()->create([
                'status' => $trackingStatus,
                'location' => Auth::check() ? ('Updated by ' . Auth::user()->name) : 'System Update',
                'notes' => $validated['notes'] ?? null
            ]);
        }

        // Update the order status
        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Order status updated successfully');
    }

    /**
     * Remove the specified order from storage.
     */
    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
    }

    /**
     * Get order statistics for the dashboard
     */
    public function getOrderStats()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'recent_orders' => Order::with(['supplier', 'wholesaler'])
                ->latest()
                ->take(5)
                ->get()
        ];

        return response()->json($stats);
    }

    /**
     * Display the vendor order management dashboard
     */
    public function vendorIndex()
    {
        Log::info('VendorIndex method called');
        
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        if (!$wholesaler) {
            Log::error('Wholesaler not found in vendorIndex');
            return redirect()->route('dashboard')->with('error', 'Vendor profile not found.');
        }

        // Get orders placed by this vendor/wholesaler
        $orders = Order::with(['coffeeProduct', 'orderTrackings'])
            ->where('wholesaler_id', $wholesaler->id)
            ->latest()
            ->paginate(15);

        $statuses = $this->getStatuses();
        $orderStats = $this->getVendorOrderStats($wholesaler->id);

        Log::info('VendorIndex returning view', ['orders_count' => $orders->count()]);
        
        return view('orders.vendor.index', compact('orders', 'statuses', 'orderStats'));
    }

    /**
     * Show the form for creating a new vendor order
     */
    public function vendorCreate()
    {
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        if (!$wholesaler) {
            return redirect()->route('dashboard')->with('error', 'Vendor profile not found.');
        }

        $coffeeProducts = CoffeeProduct::with('rawCoffee')->get();
        
        return view('orders.vendor.create', compact('coffeeProducts'));
    }

    /**
     * Store a newly created vendor order
     */
    public function vendorStore(LaravelRequest $request)
    {
        try {
            // Add debugging at the start
            \Log::info('OrderController::vendorStore called', [
                'user' => Auth::user(),
                'request_data' => $request->all(),
                'method' => $request->method(),
                'url' => $request->url()
            ]);
            
            // Debug logging
            Log::info('VendorStore method called', [
                'user' => Auth::user(),
                'request_data' => $request->all()
            ]);
            
            Log::info('VendorStore - Getting user and wholesaler');
            $user = Auth::user();
            $wholesaler = $user->wholesaler;
            
            if (!$wholesaler) {
                Log::error('Wholesaler not found for user', ['user_id' => $user->id]);
                return redirect()->route('dashboard')->with('error', 'Vendor profile not found.');
            }
            
            Log::info('VendorStore - Wholesaler found', ['wholesaler_id' => $wholesaler->id]);

            Log::info('VendorStore - Starting validation');
            $validated = $request->validate([
                'coffee_product_id' => 'required|exists:coffee_product,id',
                'quantity' => 'required|numeric|min:1',
                'notes' => 'nullable|string|max:500',
            ]);
            
            Log::info('VendorStore - Validation passed', $validated);

            Log::info('VendorStore - Finding coffee product');
            $coffeeProduct = CoffeeProduct::findOrFail($validated['coffee_product_id']);
            Log::info('VendorStore - Coffee product found', ['product_id' => $coffeeProduct->id]);
            
            Log::info('VendorStore - Calculating price');
            $totalPrice = $coffeeProduct->calculatePrice($validated['quantity']);
            Log::info('VendorStore - Price calculated', ['total_price' => $totalPrice]);

            Log::info('VendorStore - Creating order');
            $order = Order::create([
                'wholesaler_id' => $wholesaler->id,
                'coffee_product_id' => $validated['coffee_product_id'],
                'quantity' => $validated['quantity'],
                'total_price' => $totalPrice,
                'total_amount' => $totalPrice,
                'status' => 'pending',
                'order_date' => now(),
                'notes' => $validated['notes'] ?? null
            ]);
            
            Log::info('VendorStore - Order created successfully');
            // Skip refresh since it's causing issues - the DB trigger handles the ID
            // $order->refresh();

            // Get the latest order for this wholesaler to get the ID
            $latestOrder = Order::where('wholesaler_id', $wholesaler->id)
                ->latest('created_at')
                ->first();

            $orderId = $latestOrder ? $latestOrder->id : 'Unknown';
            Log::info('Order created successfully', ['order_id' => $orderId]);

            Log::info('VendorStore - Redirecting to vendor orders index');
            // Redirect to vendor orders list instead of back
            return redirect()->route('orders.vendor.index')->with('success', 'Order placed successfully! Order ID: ' . $orderId);
            
        } catch (\Exception $e) {
            Log::error('VendorStore - Exception caught', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Error creating order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified vendor order
     */
    public function vendorShow(Order $order)
    {
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        if (!$wholesaler || $order->wholesaler_id !== $wholesaler->id) {
            return redirect()->route('orders.vendor.index')->with('error', 'Order not found.');
        }

        $order->load(['coffeeProduct', 'orderTrackings']);
        return view('orders.vendor.show', compact('order'));
    }

    /**
     * Cancel a vendor order (only if status is pending)
     */
    public function vendorCancel(Order $order)
    {
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        if (!$wholesaler || $order->wholesaler_id !== $wholesaler->id) {
            return redirect()->route('orders.vendor.index')->with('error', 'Order not found.');
        }

        if ($order->status !== 'pending') {
            return redirect()->route('orders.vendor.index')->with('error', 'Only pending orders can be cancelled.');
        }

        $order->update(['status' => 'cancelled']);

        return redirect()->route('orders.vendor.index')->with('success', 'Order cancelled successfully.');
    }

    /**
     * Get vendor order statistics
     */
    private function getVendorOrderStats($wholesalerId)
    {
        $orders = Order::where('wholesaler_id', $wholesalerId);
        
        return [
            'total_orders' => $orders->count(),
            'pending_orders' => $orders->where('status', 'pending')->count(),
            'confirmed_orders' => $orders->where('status', 'confirmed')->count(),
            'shipped_orders' => $orders->where('status', 'shipped')->count(),
            'delivered_orders' => $orders->where('status', 'delivered')->count(),
            'cancelled_orders' => $orders->where('status', 'cancelled')->count(),
            'total_spent' => $orders->sum('total_amount'),
        ];
    }

    /**
     * Helper to get status keys for validation
     */
    private function getStatuses()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
    }
}