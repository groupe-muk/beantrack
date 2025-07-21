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
        
        // Get all raw coffee items that belong to this supplier
        $rawCoffeeItems = RawCoffee::where('supplier_id', $supplier->id)->get();

        // Get all unique coffee types and grades from this supplier's raw coffee
        $coffeeTypes = RawCoffee::where('supplier_id', $supplier->id)
            ->distinct()
            ->pluck('coffee_type')
            ->toArray();
        
        $grades = RawCoffee::where('supplier_id', $supplier->id)
            ->distinct()
            ->pluck('grade')
            ->toArray();

        $typeGradeQuantities = [];
        $typeGradeTrends = [];
        
        foreach ($coffeeTypes as $type) {
            foreach ($grades as $grade) {
                $quantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                    ->whereIn('inventory.warehouse_id', $warehouses->pluck('id'))
                    ->where('raw_coffee.coffee_type', $type)
                    ->where('raw_coffee.grade', $grade)
                    ->where('raw_coffee.supplier_id', $supplier->id)
                    ->sum('inventory.quantity_in_stock');
                
                $typeGradeQuantities["{$type}_{$grade}"] = $quantity;
                $typeGradeTrends["{$type}_{$grade}"] = $this->calculateTrend($type, $grade, $warehouses->pluck('id'));
            }
        }

        // Calculate total quantities dynamically
        $totalQuantity = 0;
        $coffeeTypeQuantities = [];
        $coffeeTypeTrends = [];
        
        foreach ($coffeeTypes as $type) {
            $typeQuantity = 0;
            foreach ($grades as $grade) {
                $typeQuantity += $typeGradeQuantities["{$type}_{$grade}"] ?? 0;
            }
            $coffeeTypeQuantities[$type] = $typeQuantity;
            $coffeeTypeTrends[$type] = $this->calculateTrend($type, null, $warehouses->pluck('id'));
            $totalQuantity += $typeQuantity;
        }
        
         // Create inventory items for the table - show all coffee types for this supplier
        $inventoryItems = collect();
        foreach ($coffeeTypes as $type) {
            $totalTypeQuantity = $coffeeTypeQuantities[$type] ?? 0;
            
            // Find a raw coffee item with this type to use its ID
            $rawCoffee = RawCoffee::where('coffee_type', $type)
                ->where('supplier_id', $supplier->id)
                ->first();
            
            if ($rawCoffee) {
                $inventoryItems->push((object)[
                    'id' => preg_replace('/[^0-9]/', '', $rawCoffee->id), // Extract only numbers from ID
                    'name' => $type,
                    'total_quantity' => $totalTypeQuantity
                ]);
            }
        }

        // Define products variable for form dropdowns - only show this supplier's raw coffee
        $products = RawCoffee::where('supplier_id', $supplier->id)
            ->select('id', 'coffee_type as name', 'grade')
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
            'totalQuantity',
            'typeGradeQuantities',
            'typeGradeTrends',
            'coffeeTypes',
            'grades',
            'inventoryItems',
            'products',
            'coffeeTypeQuantities',
            'coffeeTypeTrends'
        ));
    }

    private function calculateTrend($type, $grade = null, $warehouseIds = null)
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        $currentWeek = now()->startOfWeek();
        $previousWeek = now()->subWeek()->startOfWeek();
        
        $query = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
            ->where('raw_coffee.coffee_type', $type);
            
        if ($supplier) {
            $query->where('raw_coffee.supplier_id', $supplier->id);
        }
            
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
            'raw_coffee_id' => 'required|exists:raw_coffee,id',
            'quantity_in_stock' => 'required|numeric|min:0',
            'supply_center_id' => 'required|exists:warehouses,id', // This is actually warehouse_id for suppliers
            'defect_count' => 'nullable|integer|min:0',
        ]);

        // Verify the warehouse belongs to this supplier
        $warehouse = Warehouse::where('id', $validated['supply_center_id'])
            ->where('supplier_id', $supplier->id)
            ->first();
            
        if (!$warehouse) {
            return redirect()->route('supplierInventory.index')
                ->with('error', 'Invalid warehouse selected.');
        }
        
        // For suppliers, we don't create new raw coffee records - they work with existing ones
        // The defect_count is for information only and doesn't modify the raw coffee record
        
        // Create the inventory item in supplier's warehouse
        $inventory = new Inventory();
        $inventory->raw_coffee_id = $validated['raw_coffee_id'];
        $inventory->quantity_in_stock = $validated['quantity_in_stock'];
        $inventory->warehouse_id = $validated['supply_center_id']; // Supplier's warehouse
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
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return response()->json(['error' => 'Supplier profile not found'], 400);
        }

        $warehouses = Warehouse::where('supplier_id', $supplier->id)->pluck('id');

        // Get all raw coffee items of this type for this supplier
        $rawCoffeeItems = RawCoffee::where('coffee_type', $type)
            ->where('supplier_id', $supplier->id)
            ->get();

        // Get all grades available for this coffee type
        $availableGrades = $rawCoffeeItems->pluck('grade')->unique()->toArray();
        
        $gradeBreakdown = [];
        $totalQuantity = 0;

        foreach ($availableGrades as $grade) {
            // Get inventory for this specific grade
            $gradeInventories = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->join('warehouses', 'inventory.warehouse_id', '=', 'warehouses.id')
                ->whereIn('inventory.warehouse_id', $warehouses)
                ->where('raw_coffee.coffee_type', $type)
                ->where('raw_coffee.grade', $grade)
                ->where('raw_coffee.supplier_id', $supplier->id)
                ->select(
                    'inventory.id',
                    'inventory.quantity_in_stock',
                    'warehouses.name as warehouse_name',
                    'inventory.updated_at'
                )
                ->get();

            $gradeTotal = $gradeInventories->sum('quantity_in_stock');
            $totalQuantity += $gradeTotal;

            $warehouseDetails = $gradeInventories->map(function($item) {
                return [
                    'id' => $item->id,
                    'warehouse' => $item->warehouse_name,
                    'quantity' => $item->quantity_in_stock,
                    'last_updated' => $item->updated_at->format('Y-m-d H:i:s')
                ];
            })->toArray();

            $gradeBreakdown[] = [
                'grade' => $grade,
                'total_quantity' => $gradeTotal,
                'warehouse_details' => $warehouseDetails
            ];
        }

        return response()->json([
            'coffee_type' => $type,
            'total_quantity' => $totalQuantity,
            'grade_breakdown' => $gradeBreakdown
        ]);
    }

    /**
     * Get a specific inventory item for editing
     */
    public function getItem($id)
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return response()->json(['error' => 'Supplier profile not found.'], 404);
        }

        // Get supplier's warehouse IDs
        $warehouseIds = Warehouse::where('supplier_id', $supplier->id)->pluck('id');
        
        // Get the inventory item, ensuring it belongs to this supplier's warehouses
        $inventoryItem = Inventory::with(['rawCoffee', 'warehouse'])
            ->whereIn('warehouse_id', $warehouseIds)
            ->findOrFail($id);

        return response()->json([
            'id' => $inventoryItem->id,
            'raw_coffee_id' => $inventoryItem->raw_coffee_id,
            'grade' => $inventoryItem->rawCoffee->grade ?? null,
            'coffee_type' => $inventoryItem->rawCoffee->coffee_type ?? null,
            'quantity_in_stock' => $inventoryItem->quantity_in_stock,
            'supply_center_id' => $inventoryItem->warehouse_id,
            'warehouse_name' => $inventoryItem->warehouse->location ?? 'Unknown',
        ]);
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

    /**
     * Create a new raw coffee type (Supplier only)
     */
    public function createRawCoffee(Request $request)
    {
        $user = Auth::user();
        $supplier = Supplier::where('user_id', $user->id)->first();
        
        if (!$supplier) {
            return redirect()->route('supplierInventory.index')
                ->with('error', 'Supplier profile not found.');
        }

        $validated = $request->validate([
            'coffee_type' => 'required|string|max:50',
            'grades' => 'required|array|min:1',
            'grades.*' => 'string|in:A,B,C,AA,Premium',
            'screen_size' => 'nullable|string|max:10',
            'defect_count' => 'nullable|integer|min:0',
        ]);

        // Create a raw coffee record for each selected grade
        $createdGrades = [];
        foreach ($validated['grades'] as $grade) {
            // Check if this supplier already has this coffee type and grade combination
            $existingRawCoffee = RawCoffee::where('supplier_id', $supplier->id)
                ->where('coffee_type', $validated['coffee_type'])
                ->where('grade', $grade)
                ->first();

            if (!$existingRawCoffee) {
                // Create the new raw coffee record
                RawCoffee::create([
                    'supplier_id' => $supplier->id,
                    'coffee_type' => $validated['coffee_type'],
                    'grade' => $grade,
                    'screen_size' => $validated['screen_size'],
                    'defect_count' => $validated['defect_count'] ?? 0,
                    'harvest_date' => null, // Will be set when inventory batches are added
                ]);
                $createdGrades[] = $grade;
            }
        }

        $message = count($createdGrades) > 0 
            ? 'New raw coffee created successfully for grades: ' . implode(', ', $createdGrades)
            : 'Raw coffee creation completed (some grades may already exist)';

        return redirect()->route('supplierInventory.index')
            ->with('success', $message);
    }
}





