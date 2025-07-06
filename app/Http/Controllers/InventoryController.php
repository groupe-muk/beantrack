<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\SupplyCenter;

class InventoryController extends Controller
{
    // Get all inventory items
    public function index()
    {
        $rawCoffeeInventory = Inventory::with(['rawCoffee', 'supplyCenter'])->whereNotNull('raw_coffee_id')->get();
        $coffeeProductInventory = Inventory::with(['coffeeProduct', 'supplyCenter'])->whereNotNull('coffee_product_id')->get();
        $supplyCenters = SupplyCenter::all();
        $rawCoffeeItems = RawCoffee::all();
        $coffeeProductItems = CoffeeProduct::all();

        return view('Inventory.inventory', compact(['rawCoffeeInventory', 'coffeeProductInventory', 'supplyCenters', 'rawCoffeeItems', 'coffeeProductItems']));
    }

    // Store a new inventory item
    public function store(Request $request)
    {
        $request->validate([
            'supply_center_id' => 'required|exists:supply_centers,id',
            'quantity_in_stock' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        
        // Handle quantity field mapping if needed
        if (isset($data['quantity'])) {
            $data['quantity_in_stock'] = $data['quantity'];
            unset($data['quantity']);
        }

        try {
            Inventory::create($data);
            return redirect()->route('inventory.index')->with('success', 'Item added successfully!');
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to add item: ' . $e->getMessage());
        }
    }

    // Update Raw Coffee inventory
    public function updateRawCoffee(Request $request, $rawCoffeeId)
    {
        $request->validate([
            'quantity_in_stock' => 'required|numeric|min:0',
            'supply_center_id' => 'required|exists:supply_centers,id',
        ]);

        try {
            $inventory = Inventory::where('raw_coffee_id', $rawCoffeeId)->firstOrFail();
            $inventory->update($request->only(['quantity_in_stock', 'supply_center_id']));
            
            return redirect()->route('inventory.index')->with('success', 'Raw coffee item updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    // Update Coffee Product inventory
    public function updateCoffeeProduct(Request $request, $coffeeProductId)
    {
        $request->validate([
            'quantity_in_stock' => 'required|numeric|min:0',
            'supply_center_id' => 'required|exists:supply_centers,id',
        ]);

        try {
            $inventory = Inventory::where('coffee_product_id', $coffeeProductId)->firstOrFail();
            $inventory->update($request->only(['quantity_in_stock', 'supply_center_id']));
            
            return redirect()->route('inventory.index')->with('success', 'Coffee product updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    // Delete Raw Coffee inventory
    public function destroyRawCoffee($rawCoffeeId)
    {
        try {
            $inventory = Inventory::where('raw_coffee_id', $rawCoffeeId)->firstOrFail();
            $inventory->delete();
            
            return redirect()->route('inventory.index')->with('success', 'Raw coffee item deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    // Delete Coffee Product inventory
    public function destroyCoffeeProduct($coffeeProductId)
    {
        try {
            $inventory = Inventory::where('coffee_product_id', $coffeeProductId)->firstOrFail();
            $inventory->delete();
            
            return redirect()->route('inventory.index')->with('success', 'Coffee product deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    // Show a single item
    public function show($id)
    {
        return Inventory::findOrFail($id);
    }

    // Generic update method (keep for backward compatibility)
    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    // Generic destroy method (keep for backward compatibility)
    public function destroy($id)
    {
        Inventory::destroy($id);
        return redirect()->route('inventory.index')->with('success', 'Item deleted!');
    }

    public function edit($coffeeProduct)
    {
        $product = CoffeeProduct::findOrFail($coffeeProduct);
        return view('Inventory.edit', compact('product'));
    }
}