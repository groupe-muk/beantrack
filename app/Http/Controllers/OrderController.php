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
     * Helper to get status keys for validation
     */
    private function getStatuses()
    {
        return [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'confirmed' => 'bg-blue-100 text-blue-800',
            'shipped' => 'bg-purple-100 text-purple-800',
            'delivered' => 'bg-green-100 text-green-800',
        ];
    }
}