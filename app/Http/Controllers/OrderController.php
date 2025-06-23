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
use Illuminate\Validation\Rule; // Add this for unique validation if needed

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
        return view('orders.index', compact('orders', 'statuses'));
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
            'supplier_id' => 'required|exists:suppliers,id',
            'grade' => 'required|string',
            'coffee_type' => 'required|string',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'status' => ['required', Rule::in(array_keys($this->getStatuses()))], // Reuse status keys
            'notes' => 'nullable|string|max:500',
        ]);

        // Find the raw coffee that matches both grade and coffee_type
        $rawCoffee = RawCoffee::where('grade', $validated['grade'])
            ->where('coffee_type', $validated['coffee_type'])
            ->firstOrFail();

        $order = Order::create([
            'supplier_id' => $validated['supplier_id'],
            'raw_coffee_id' => $rawCoffee->id,
            'order_date' => $validated['order_date'],
            'total_amount' => $validated['total_amount'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null
        ]);

        // Create an initial tracking record
        $order->orderTrackings()->create([
            'status' => $validated['status'],
            'location' => 'Order created',
            'notes' => 'Initial order creation'
        ]);

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
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
        $suppliers = Supplier::pluck('name', 'id');
        $wholesalers = Wholesaler::pluck('name', 'id');
        $rawCoffees = RawCoffee::pluck('name', 'id');
        $coffeeProducts = CoffeeProduct::pluck('name', 'id');

        return view('orders.edit', compact('order', 'suppliers', 'wholesalers', 'rawCoffees', 'coffeeProducts'));
    }

    /**
     * Update the specified order in storage.
     */
    public function update(LaravelRequest $request, Order $order)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|exists:suppliers,id',
            'wholesaler_id' => 'nullable|exists:wholesalers,id',
            'raw_coffee_id' => 'nullable|exists:raw_coffees,id',
            'coffee_product_id' => 'nullable|exists:coffee_products,id',
            'order_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            // Do not allow status update here directly, use updateStatus for that
            'notes' => 'nullable|string|max:500',
        ]);

        $order->update($validated);

        return redirect()->route('orders.show', $order)->with('success', 'Order updated successfully.');
    }

    /**
     * Update the order status
     */
    public function updateStatus(LaravelRequest $request, Order $order)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->getStatuses()))],
            'notes' => 'nullable|string|max:500',
        ]);

        // Create a new tracking record
        $order->orderTrackings()->create([
            'status' => $validated['status'],
            'location' => Auth::check() ? ('Updated by ' . Auth::user()->name) : 'System Update', // Handle unauthenticated user
            'notes' => $validated['notes'] ?? null
        ]);

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
     * Helper to get status keys for validation
     */
    private function getStatuses()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-red-100 text-red-800',
        ];
    }
}