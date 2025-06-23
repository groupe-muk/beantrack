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
        $coffeeProductInventory = Inventory::with('coffeeProduct', 'supplyCenter')->whereNotNull('coffee_product_id')->get();

        return view('Inventory.inventory', compact(['rawCoffeeInventory', 'coffeeProductInventory']));
    }

    // Store a new inventory item
    public function store(Request $request)
    {
        $item = Inventory::create($request->all());
        return response()->json($item, 201);
    }

    // (Optional) Show a single item
    public function show($id)
    {
        return Inventory::findOrFail($id);
    }

    // (Optional) Update an item
    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    // (Optional) Delete an item
    public function destroy($id)
    {
        Inventory::destroy($id);
        return response()->json(null, 204);
    }
}

