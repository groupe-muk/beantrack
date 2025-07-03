<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\SupplyCenter;

class supplierInventoryController extends Controller
{
    //your logic here
    public function index()
    {
        $rawCoffeeInventory = Inventory::with(['rawCoffee', 'supplyCenter'])->whereNotNull('raw_coffee_id')->get();
        $coffeeProductInventory = Inventory::with('coffeeProduct', 'supplyCenter')->whereNotNull('coffee_product_id')->get();
        $supplyCenters = SupplyCenter::all();
        $rawCoffeeItems = RawCoffee::all();
        $coffeeProductItems = CoffeeProduct::all();


        return view('Inventory.supplierInventory', compact(
            'rawCoffeeInventory',
            'rawCoffeeItems',
            'supplyCenters'

        ));
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
        return redirect()->route('supplierInventory.index')->with('success', 'Item added!');
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
        return redirect()->route('supplierInventory.index')->with('success', 'Item deleted!');
    }
}



