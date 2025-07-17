<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\RawCoffee;
use App\Models\CoffeeProduct;
use App\Models\Warehouse;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class supplierInventoryController extends Controller
{
    //your logic here
    public function index()
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return redirect()->route('dashboard')->with('error', 'Supplier profile not found.');
        }

        // Get supplier's warehouses
        $warehouses = Warehouse::where('supplier_id', $supplier->id)->get();
        
        // Get inventory items from supplier's warehouses
        $rawCoffeeInventory = Inventory::with(['rawCoffee', 'warehouse'])
            ->whereNotNull('raw_coffee_id')
            ->whereIn('warehouse_id', $warehouses->pluck('id'))
            ->get();
        
        $rawCoffeeItems = RawCoffee::all();

        

        // Calculate quantities for each coffee type and grade from supplier's warehouses
        $coffeeTypes = ['Arabica', 'Robusta'];
        $grades = ['A', 'B'];
        
        $typeGradeQuantities = [];
        $typeGradeTrends = [];
        
        foreach ($coffeeTypes as $type) {
            foreach ($grades as $grade) {
                $quantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                    ->whereIn('inventory.warehouse_id', $warehouses->pluck('id'))
                    ->where('raw_coffee.coffee_type', $type)
                    ->where('raw_coffee.grade', $grade)
                    ->sum('inventory.quantity_in_stock');
                
                $typeGradeQuantities["{$type}_{$grade}"] = $quantity;
                $typeGradeTrends["{$type}_{$grade}"] = $this->calculateTrend($type, $grade, $warehouses->pluck('id'));
            }
        }

        // Calculate total quantities for cards
        $arabicaQuantity = $typeGradeQuantities['Arabica_A'] + $typeGradeQuantities['Arabica_B'];
        $robustaQuantity = $typeGradeQuantities['Robusta_A'] + $typeGradeQuantities['Robusta_B'];
        $totalQuantity = $arabicaQuantity + $robustaQuantity;

        // Calculate trends for cards
        $arabicaTrend = $this->calculateTrend('Arabica', null, $warehouses->pluck('id'));
        $robustaTrend = $this->calculateTrend('Robusta', null, $warehouses->pluck('id'));
        
         // Create inventory items for the table
    $inventoryItems = collect();
    foreach ($coffeeTypes as $type) {
        $totalTypeQuantity = $typeGradeQuantities["{$type}_A"] + $typeGradeQuantities["{$type}_B"];
        
        // Find a raw coffee item with this type to use its ID
        $rawCoffee = RawCoffee::where('coffee_type', $type)->first();
        
        if ($rawCoffee) {
            $inventoryItems->push((object)[
                'id' => preg_replace('/[^0-9]/', '', $rawCoffee->id), // Extract only numbers from ID
                'name' => $type,
                'total_quantity' => $totalTypeQuantity
            ]);
        }
    }

    // Define products variable for form dropdowns
    $products = RawCoffee::select('id', 'coffee_type as name', 'grade')
        ->get()
        ->map(function($item) {
            return (object)[
                'id' => $item->id,
                'name' => "{$item->name} (Grade {$item->grade})"
            ];
        });

        return view('Inventory.supplierInventory', compact(
            'rawCoffeeInventory',
            'rawCoffeeItems',
            'warehouses',
            'arabicaQuantity',
            'arabicaTrend',
            'robustaQuantity',
            'robustaTrend',
            'totalQuantity',
            'typeGradeQuantities',
            'typeGradeTrends',
            'coffeeTypes',
            'grades',
            'inventoryItems',
            'products'


        ));
    }

    private function calculateTrend($type, $grade = null, $warehouseIds = null)
    {
        $currentWeek = now()->startOfWeek();
        $previousWeek = now()->subWeek()->startOfWeek();
        
        $query = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
            ->where('raw_coffee.coffee_type', $type);
            
        if ($grade) {
            $query->where('raw_coffee.grade', $grade);
        }
        
        if ($warehouseIds) {
            $query->whereIn('inventory.warehouse_id', $warehouseIds);
        }
        
        $currentQuantity = (clone $query)
            ->where('inventory.updated_at', '>=', $currentWeek)
            ->sum('inventory.quantity_in_stock');
            
        $previousQuantity = (clone $query)
            ->whereBetween('inventory.updated_at', [$previousWeek, $currentWeek])
            ->sum('inventory.quantity_in_stock');
        
        if ($previousQuantity == 0) {
            return $currentQuantity > 0 ? 100 : 0;
        }
        
        $change = (($currentQuantity - $previousQuantity) / $previousQuantity) * 100;
        return round($change, 1);
    }
    // Store a new inventory item
    public function store(Request $request)
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return redirect()->route('supplierInventory.index')
                ->with('error', 'Supplier profile not found.');
        }

        $validated = $request->validate([
            'raw_coffee_id' => 'required',
            'quantity_in_stock' => 'required|numeric|min:0',
            'supply_center_id' => 'required|exists:warehouses,id'
        ]);

        // Verify the warehouse belongs to this supplier
        $warehouse = Warehouse::where('id', $validated['supply_center_id'])
            ->where('supplier_id', $supplier->id)
            ->first();
            
        if (!$warehouse) {
            return redirect()->route('supplierInventory.index')
                ->with('error', 'Invalid warehouse selected.');
        }
        
        // Create the inventory item
        $inventory = new Inventory();
        $inventory->raw_coffee_id = $validated['raw_coffee_id'];
        $inventory->quantity_in_stock = $validated['quantity_in_stock'];
        $inventory->warehouse_id = $validated['supply_center_id'];
        $inventory->save();
        
        return redirect()
            ->route('supplierInventory.index')
            ->with('success', 'Inventory item added successfully');
}

        
    
    public function edit($id)
    {
        $inventory = Inventory::with(['warehouse', 'rawCoffee'])
            ->findOrFail($id);

        return response()->json([
            'id' => $inventory->id,
            'quantity_in_stock' => $inventory->quantity_in_stock,
            'warehouse_name' => $inventory->warehouse->name,
            'coffee_type' => $inventory->rawCoffee->coffee_type,
            'grade' => $inventory->rawCoffee->grade
        ]);
    }
    
    public function update(Request $request, $id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            
            $validated = $request->validate([
                'quantity_in_stock' => 'required|numeric|min:0|regex:/^\d*\.?\d{0,2}$/',
                'warehouse_name' => 'required|string'
            ]);

            // Get user's warehouse by name
            $user = Auth::user();
            $supplier = Supplier::where('user_id', $user->id)->first();
            $warehouse = Warehouse::where('supplier_id', $supplier->id)
                ->where('name', $validated['warehouse_name'])
                ->firstOrFail();

            $inventory->quantity_in_stock = $validated['quantity_in_stock'];
            $inventory->warehouse_id = $warehouse->id;
            $inventory->save();

            return response()->json([
                'success' => true,
                'message' => 'Inventory updated successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error updating inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating inventory: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inventory deleted successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting inventory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting inventory: ' . $e->getMessage()
            ], 500);
        }
    }
     public function stats()
    {
        $lowStockThreshold = 10;

        $outOfStock = Inventory::where('quantity_in_stock', 0)->count();

        // Calculate low stock for items that are in stock but below the threshold
        $lowStock = Inventory::where('quantity_in_stock', '>', 0)
            ->where('quantity_in_stock', '<=', $lowStockThreshold)
            ->count();

        $totalQuantity = Inventory::sum('quantity_in_stock');

        return response()->json([
            'outOfStock' => $outOfStock,
            'lowStock' => $lowStock,
            'totalQuantity' => number_format($totalQuantity)
        ]);
    }

    public function getDetails($type)
    {
        // Get quantities for each grade
        $gradeQuantities = [
            'A' => Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->where('raw_coffee.coffee_type', $type)
                ->where('raw_coffee.grade', 'A')
                ->sum('inventory.quantity_in_stock'),
            'B' => Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->where('raw_coffee.coffee_type', $type)
                ->where('raw_coffee.grade', 'B')
                ->sum('inventory.quantity_in_stock')
        ];

                // Get all inventory items for this coffee type
        $inventoryItems = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
            ->join('warehouses', 'inventory.warehouse_id', '=', 'warehouses.id')
            ->where('raw_coffee.coffee_type', $type)
            ->select(
                'inventory.id',
                'inventory.quantity_in_stock',
                'raw_coffee.grade',
                'warehouses.name as warehouse'
            )
            ->orderBy('inventory.updated_at', 'desc')
            ->get();

        return response()->json([
            'coffee_type' => $type,
            'gradeQuantities' => $gradeQuantities,
            'inventoryItems' => $inventoryItems
        ]);
    }

    /**
     * Get a specific inventory item for editing
     */
    public function getItem($id)
    {
        $inventoryItem = Inventory::findOrFail($id);
        return response()->json($inventoryItem);
    }

    /**
     * Update a specific inventory item
     */
    public function updateItem(Request $request, $id)
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return redirect()->route('supplierInventory.index')
                ->with('error', 'Supplier profile not found.');
        }

        $validated = $request->validate([
            'raw_coffee_id' => 'required',
            'quantity_in_stock' => 'required|numeric|min:0',
            'supply_center_id' => 'required|exists:warehouses,id',
            'grade' => 'required|string|max:10',
        ]);

        // Verify the warehouse belongs to this supplier
        $warehouse = Warehouse::where('id', $validated['supply_center_id'])
            ->where('supplier_id', $supplier->id)
            ->first();
            
        if (!$warehouse) {
            return redirect()->route('supplierInventory.index')
                ->with('error', 'Invalid warehouse selected.');
        }
        
        $inventoryItem = Inventory::findOrFail($id);
        $inventoryItem->raw_coffee_id = $validated['raw_coffee_id'];
        $inventoryItem->quantity_in_stock = $validated['quantity_in_stock'];
        $inventoryItem->warehouse_id = $validated['supply_center_id'];
        $inventoryItem->save();
        
        return redirect()
            ->route('supplierInventory.index')
            ->with('success', 'Inventory item updated successfully');
    }

    /**
     * Delete a specific inventory item
     */
    public function deleteItem($id)
    {
        $inventoryItem = Inventory::findOrFail($id);
        $inventoryItem->delete();
        
        return redirect()
            ->route('supplierInventory.index')
            ->with('success', 'Inventory item deleted successfully');
    }
}





