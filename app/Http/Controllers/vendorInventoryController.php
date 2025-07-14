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
        // Get all inventory items with their relationships
        $inventoryItems = Inventory::with(['coffeeProduct', 'supplyCenter'])
            ->whereNotNull('coffee_product_id')
            ->get();

        // Group by coffee product and calculate totals
        $uniqueProducts = $inventoryItems->groupBy('coffee_product_id')
            ->map(function($group) {
                $firstItem = $group->first();
                return [
                    'id' => $firstItem->coffeeProduct->id,
                    'name' => $firstItem->coffeeProduct->name,
                    'total_quantity' => $group->sum('quantity_in_stock'),
                    'categories' => $group->pluck('coffeeProduct.category')->unique()
                ];
            })->values();

        $supplyCenters = SupplyCenter::all();
        $coffeeProductItems = CoffeeProduct::all();

        // Calculate quantities for specific products
        $mountainBlendQuantity = $inventoryItems
            ->filter(function($item) {
                return $item->coffeeProduct && $item->coffeeProduct->name === 'Mountain Blend';
            })
            ->sum('quantity_in_stock');
            
        $morningBrewQuantity = $inventoryItems
            ->filter(function($item) {
                return $item->coffeeProduct && $item->coffeeProduct->name === 'Morning Brew';
            })
            ->sum('quantity_in_stock');

        $totalQuantity = $mountainBlendQuantity + $morningBrewQuantity;

        // Calculate percentage changes
        $mountainBlendChange = $this->calculatePercentageChange('Mountain Blend');
        $morningBrewChange = $this->calculatePercentageChange('Morning Brew');
        $totalChange = (($mountainBlendChange + $morningBrewChange) / 2); // Average change

        return view('Inventory.vendorInventory', compact(
            'uniqueProducts',
            'supplyCenters',
            'coffeeProductItems',
            'mountainBlendQuantity',
            'morningBrewQuantity',
            'totalQuantity',
            'mountainBlendChange',
            'morningBrewChange',
            'totalChange',
            'inventoryItems'
        ));
    }

    private function calculatePercentageChange($productName)
    {
        // Get current quantity
        $currentQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
            ->where('coffee_product.name', $productName)
            ->sum('inventory.quantity_in_stock');

        // Get quantity from a week ago
        $lastWeekQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
            ->where('coffee_product.name', $productName)
            ->where('inventory.updated_at', '<=', now()->subWeek())
            ->sum('inventory.quantity_in_stock');

        // Return the absolute change in quantity
        return $currentQuantity - $lastWeekQuantity;
    }
    
    // Store a new inventory item
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'coffee_product_id' => 'required|exists:coffee_product,id',
                'supply_center_id' => 'required|exists:supply_centers,id',
                'quantity_in_stock' => 'required|numeric|min:0',
                'category' => 'required|string|in:premium,standard'
            ]);

            // Create inventory record with the validated data
            $inventory = new Inventory();
            $inventory->coffee_product_id = $validatedData['coffee_product_id'];
            $inventory->supply_center_id = $validatedData['supply_center_id'];
            $inventory->quantity_in_stock = $validatedData['quantity_in_stock'];
            $inventory->category = $validatedData['category'];
            $inventory->save();

            return redirect()->route('vendorInventory.index')
                ->with('success', 'Item added successfully!');
        } catch (\Exception $e) {
            \Log::error('Error adding inventory item:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('vendorInventory.index')
                ->with('error', 'Failed to add item. Please try again.');
        }
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
        try {
            $inventory = Inventory::findOrFail($id);
            
            // Delete the inventory item
            $inventory->delete();
            
            if (request()->wantsJson()) {
                return response()->json(['message' => 'Inventory item deleted successfully'], 200);
            }
            
            return redirect()->route('vendorInventory.index')
                ->with('success', 'Inventory item deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Error deleting inventory item:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Failed to delete inventory item'], 500);
            }
            
            return redirect()->route('vendorInventory.index')
                ->with('error', 'Failed to delete inventory item');
        }
    }

    public function stats()
    {
        // Calculate quantities for specific products
        $mountainBlendQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
            ->where('coffee_product.name', 'Mountain Blend')
            ->sum('inventory.quantity_in_stock');
            
        $morningBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
            ->where('coffee_product.name', 'Morning Brew')
            ->sum('inventory.quantity_in_stock');

        $totalQuantity = $mountainBlendQuantity + $morningBrewQuantity;

        // Calculate absolute changes
        $mountainBlendChange = $this->calculatePercentageChange('Mountain Blend');
        $morningBrewChange = $this->calculatePercentageChange('Morning Brew');
        $totalChange = $mountainBlendChange + $morningBrewChange;

        return response()->json([
            'mountainBlendQuantity' => number_format($mountainBlendQuantity, 2),
            'morningBrewQuantity' => number_format($morningBrewQuantity, 2),
            'totalQuantity' => number_format($totalQuantity, 2),
            'mountainBlendChange' => number_format($mountainBlendChange, 2),
            'morningBrewChange' => number_format($morningBrewChange, 2),
            'totalChange' => number_format($totalChange, 2)
        ]);
    }

    public function details($id)
    {
        try {
            // Find the coffee product with its relationships
            $product = CoffeeProduct::findOrFail($id);
            
            // Get all inventory entries for this product with relationships
            $inventories = Inventory::with(['coffeeProduct', 'supplyCenter'])
                ->where('coffee_product_id', $id)
                ->orderBy('created_at', 'desc')  // Order by newest first
                ->get();

            if ($inventories->isEmpty()) {
                return response()->json([
                    'id' => $product->id,
                    'name' => $product->name,
                    'total_quantity' => 0,
                    'inventory_items' => []
                ]);
            }

            // Map each inventory item
            $inventoryItems = $inventories->map(function($inventory) {
                return [
                    'id' => $inventory->id,
                    'category' => $inventory->category,  // Use the inventory category
                    'quantity' => $inventory->quantity_in_stock,
                    'warehouse' => $inventory->supplyCenter->name,
                    'last_updated' => $inventory->updated_at->format('Y-m-d H:i:s'),
                    'created_at' => $inventory->created_at->format('Y-m-d H:i:s')  // Add creation date
                ];
            });

            // Calculate total quantity for each category
            $categoryTotals = $inventories->groupBy('category')
                ->map(function($items) {
                    return $items->sum('quantity_in_stock');
                });

            return response()->json([
                'id' => $product->id,
                'name' => $product->name,
                'total_quantity' => $inventories->sum('quantity_in_stock'),
                'category_totals' => $categoryTotals,
                'inventory_items' => $inventoryItems
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in vendor inventory details:', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load product details'
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $inventory = Inventory::with(['coffeeProduct', 'supplyCenter'])
                ->findOrFail($id);
            
            return response()->json([
                'category' => $inventory->coffeeProduct->category,
                'quantity_in_stock' => $inventory->quantity_in_stock,
                'supply_center_id' => $inventory->supply_center_id
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load inventory details'], 500);
        }
    }
}



