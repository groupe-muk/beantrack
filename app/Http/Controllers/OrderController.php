<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request as LaravelRequest;
use App\Models\OrderTracking;
use App\Models\Supplier;
use App\Models\Wholesaler;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\Inventory;
use App\Models\InventoryUpdate;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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
            'total_amount' => 'required|integer|min:0',
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
            'total_amount' => 'required|integer|min:0',
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
            'status' => ['required', Rule::in(['pending', 'confirmed', 'shipped', 'delivered', 'rejected', 'cancelled'])], // Match database enum
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

        // Redirect back to the referring page (dashboard or orders page)
        return redirect()->back()->with('success', 'Order status updated successfully');
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
        
        return view('vendor.orders.index', compact('orders', 'statuses', 'orderStats'));
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
        
        return view('vendor.orders.create', compact('coffeeProducts'));
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
        $statuses = $this->getStatuses();
        return view('vendor.orders.show', compact('order', 'statuses'));
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
     * Mark a vendor order as received (only if status is shipped)
     */
    public function vendorMarkReceived(Order $order)
    {
        $user = Auth::user();
        $wholesaler = $user->wholesaler;
        
        if (!$wholesaler || $order->wholesaler_id !== $wholesaler->id) {
            return redirect()->route('orders.vendor.index')->with('error', 'Order not found.');
        }

        if ($order->status !== 'shipped') {
            return redirect()->route('orders.vendor.show', $order)->with('error', 'Only shipped orders can be marked as received.');
        }

        try {
            DB::beginTransaction();

            // Update order status
            $order->update(['status' => 'delivered']);

            // Add tracking record
            $order->orderTrackings()->create([
                'status' => 'delivered',
                'notes' => 'Order marked as received by vendor',
                'location' => 'Vendor Location',
                'updated_at' => now()
            ]);

            // Update vendor inventory
            $this->updateVendorInventory($order, $wholesaler);

            DB::commit();

            return redirect()->route('orders.vendor.show', $order)->with('success', 'Order marked as delivered successfully and inventory updated.');
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error marking order as received', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return redirect()->route('orders.vendor.show', $order)->with('error', 'Failed to mark order as received. Please try again.');
        }
    }

    /**
     * Update vendor inventory when order is received
     */
    private function updateVendorInventory(Order $order, Wholesaler $wholesaler)
    {
        try {
            Log::info('Starting inventory update', [
                'order_id' => $order->id,
                'wholesaler_id' => $wholesaler->id,
                'coffee_product_id' => $order->coffee_product_id,
                'quantity' => $order->quantity
            ]);

            // Get or create the first warehouse for this wholesaler
            $warehouse = $wholesaler->warehouses()->first();
            
            if (!$warehouse) {
                Log::info('Creating new warehouse for wholesaler', ['wholesaler_id' => $wholesaler->id]);
                
                // Create a default warehouse if none exists
                $warehouse = $wholesaler->warehouses()->create([
                    'name' => $wholesaler->name . ' - Main Warehouse',
                    'location' => $wholesaler->address ?? 'Main Location',
                    'capacity' => 10000, // Default capacity
                    'wholesaler_id' => $wholesaler->id,
                    'manager_name' => $wholesaler->contact_person ?? 'Warehouse Manager'
                ]);
                
                Log::info('Warehouse created successfully', ['warehouse_id' => $warehouse->id]);
            }

            // Find existing inventory or create new one
            $inventory = Inventory::where('coffee_product_id', $order->coffee_product_id)
                                 ->where('warehouse_id', $warehouse->id)
                                 ->first();

            if ($inventory) {
                Log::info('Updating existing inventory', ['inventory_id' => $inventory->id]);
                
                // Update existing inventory
                $oldQuantity = $inventory->quantity_in_stock;
                $newQuantity = $oldQuantity + $order->quantity;
                
                $inventory->update([
                    'quantity_in_stock' => $newQuantity,
                    'last_updated' => now()
                ]);
                
                $updateType = 'stock_increase';
                $notes = "Received order #{$order->id} - Added {$order->quantity} kg";
                
                Log::info('Inventory updated successfully', [
                    'inventory_id' => $inventory->id,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity
                ]);
            } else {
                Log::info('Creating new inventory record');
                
                // Get the coffee product to use its category
                $coffeeProduct = \App\Models\CoffeeProduct::find($order->coffee_product_id);
                if (!$coffeeProduct) {
                    throw new \Exception("Coffee product not found: {$order->coffee_product_id}");
                }
                
                $category = strtolower($coffeeProduct->category);
                // Ensure category is valid for the ENUM
                if (!in_array($category, ['premium', 'standard', 'specialty'])) {
                    $category = 'standard'; // Default fallback
                }
                
                // Create new inventory record
                $inventory = Inventory::create([
                    'coffee_product_id' => $order->coffee_product_id,
                    'category' => $category,
                    'quantity_in_stock' => $order->quantity,
                    'warehouse_id' => $warehouse->id,
                    'last_updated' => now()
                ]);
                
                $oldQuantity = 0;
                $newQuantity = $order->quantity;
                $updateType = 'new_stock';
                $notes = "Initial stock from order #{$order->id} - Added {$order->quantity} kg";
                
                Log::info('New inventory created successfully', ['inventory_id' => $inventory->id]);
            }

            // Create inventory update record for tracking
            Log::info('Creating inventory update record');
            
            $inventoryUpdate = InventoryUpdate::create([
                'inventory_id' => $inventory->id,
                'quantity_change' => $order->quantity,
                'reason' => $notes,
                'updated_by' => Auth::user()->id
            ]);
            
            Log::info('Inventory update completed successfully', [
                'inventory_update_id' => $inventoryUpdate->id ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Error in updateVendorInventory', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to be caught by the main transaction
        }
    }

    /**
     * Get vendor order statistics
     */
    private function getVendorOrderStats($wholesalerId)
    {
        $baseQuery = Order::where('wholesaler_id', $wholesalerId);
        
        return [
            'total_orders' => (clone $baseQuery)->count(),
            'pending_orders' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed_orders' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'shipped_orders' => (clone $baseQuery)->where('status', 'shipped')->count(),
            'delivered_orders' => (clone $baseQuery)->where('status', 'delivered')->count(),
            'cancelled_orders' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'total_spent' => (clone $baseQuery)->whereIn('status', ['confirmed', 'shipped', 'delivered'])->sum('total_price'),
        ];
    }

    /**
     * API: Get vendor order status updates
     */
    public function getVendorOrderStatusUpdates(LaravelRequest $request)
    {
        try {
            $vendorId = Auth::id();
            $wholesaler = Auth::user()->wholesaler;
            
            if (!$wholesaler) {
                return response()->json([
                    'success' => false,
                    'message' => 'Wholesaler not found'
                ], 404);
            }
            
            // Get orders with their current statuses
            $orders = Order::where('wholesaler_id', $wholesaler->id)
                ->orderBy('created_at', 'desc')
                ->get(['id', 'status', 'total_price', 'created_at']);

            // Get updated statistics
            $ordersQuery = Order::where('wholesaler_id', $wholesaler->id);
            
            $stats = [
                'total_orders' => (clone $ordersQuery)->count(),
                'pending_orders' => (clone $ordersQuery)->where('status', 'pending')->count(),
                'total_spent' => (clone $ordersQuery)->sum('total_price'),
                'completed_orders' => (clone $ordersQuery)->whereIn('status', ['delivered'])->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'orders' => $orders,
                    'stats' => $stats,
                    'statuses' => $this->getStatuses()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch status updates: ' . $e->getMessage()
            ], 500);
        }
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