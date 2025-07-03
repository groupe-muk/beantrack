<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoffeeProduct;
use App\Models\Inventory;
use App\Models\SupplyCenter;

class vendorInventoryController extends Controller
{
    //
    public function index()
    {
        $coffeeProductInventory = Inventory::with('coffeeProduct', 'supplyCenter')->whereNotNull('coffee_product_id')->get();
        $supplyCenters = SupplyCenter::all();
        $coffeeProductItems = CoffeeProduct::all();

        return view('Inventory.vendorInventory', compact(
            'coffeeProductInventory',
            'coffeeProductItems',
            'supplyCenters'
        ));
        // return view('Inventory.vendorInventory', compact('rawCoffeeInventory'));
    }
    
    // Store a new inventory item
    public function store(Request $request)
    {
        $data = $request->all();
        if (isset($data['quantity'])) {
        $data['quantity_in_stock'] = $data['quantity'];
        unset($data['quantity']);
        }

        Inventory::create($request->all());
        return redirect()->route('vendorInventory.index')->with('success', 'Item added!');
    }

    // (Optional) Show a single item
    public function show($id)
    {
        return Inventory::findOrFail($id);
    }

    
    public function update(Request $request, $id)
    {
        $item = Inventory::findOrFail($id);
        $item->update($request->all());
        return response()->json($item);
    }

    
    public function destroy($id)
    {
        Inventory::destroy($id);
        return redirect()->route('vendorInventory.index')->with('success', 'Item deleted!');
    }
}



