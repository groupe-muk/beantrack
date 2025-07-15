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
    
    // Get all unique coffee types and grades from the database
    $rawCoffeeTypes = RawCoffee::distinct()->pluck('coffee_type')->toArray();
    $rawCoffeeGrades = RawCoffee::distinct()->pluck('grade')->toArray();
    
    $rawCoffeeInventory = collect();
    
    foreach ($rawCoffeeTypes as $coffeeType) {
        // Find the first raw coffee record for this type (we'll use its ID for all grades)
        $firstRawCoffee = RawCoffee::where('coffee_type', $coffeeType)->first();
        
        foreach ($rawCoffeeGrades as $grade) {
            // Find existing raw coffee record with this type and grade
            $rawCoffee = RawCoffee::where('coffee_type', $coffeeType)
                ->where('grade', $grade)
                ->first();
            
            if ($rawCoffee) {
                $inventory = Inventory::where('raw_coffee_id', $rawCoffee->id)
                    ->select(
                        \DB::raw('COALESCE(SUM(quantity_in_stock), 0) as total_quantity'),
                        \DB::raw('MAX(updated_at) as last_updated')
                    )
                    ->first();
                
                $rawCoffeeInventory->push((object) [
                    'id' => $firstRawCoffee ? $firstRawCoffee->id : $rawCoffee->id,
                    'coffee_type' => $coffeeType,
                    'grade' => $grade,
                    'total_quantity' => $inventory ? $inventory->total_quantity : 0,
                    'last_updated' => $inventory ? $inventory->last_updated : null
                ]);
            } else {
                // Create a placeholder entry with 0 quantity
                $rawCoffeeInventory->push((object) [
                    'id' => $firstRawCoffee ? $firstRawCoffee->id : null,
                    'coffee_type' => $coffeeType,
                    'grade' => $grade,
                    'total_quantity' => 0,
                    'last_updated' => null
                ]);
            }
        }
    }

    // Get all unique coffee product names and categories from the database
    $coffeeProductNames = CoffeeProduct::distinct()->pluck('name')->toArray();
    $coffeeProductCategories = CoffeeProduct::distinct()->pluck('category')->toArray();
    
    $coffeeProductInventory = collect();
    
    foreach ($coffeeProductNames as $productName) {
        // Find the first coffee product record for this name
        $firstCoffeeProduct = CoffeeProduct::where('name', $productName)->first();
        
        foreach ($coffeeProductCategories as $category) {
            
                // Find existing coffee product record with this name and category
                $coffeeProduct = CoffeeProduct::where('name', $productName)
                    ->where('category', $category)
                    ->first();
                
                if ($coffeeProduct) {
                    $inventory = Inventory::where('coffee_product_id', $coffeeProduct->id)
                        ->select(
                            \DB::raw('COALESCE(SUM(quantity_in_stock), 0) as total_quantity'),
                            \DB::raw('MAX(updated_at) as last_updated')
                        )
                        ->first();
                    
                    $coffeeProductInventory->push((object) [
                        'id' => $firstCoffeeProduct ? $firstCoffeeProduct->id : $coffeeProduct->id,
                        'name' => $productName,
                        'category' => $category,
                        'total_quantity' => $inventory ? $inventory->total_quantity : 0,
                        'last_updated' => $inventory ? $inventory->last_updated : null
                    ]);
                } else {
                    // Create a placeholder entry with 0 quantity
                    $coffeeProductInventory->push((object) [
                        'id' => $firstCoffeeProduct ? $firstCoffeeProduct->id : null,
                        'name' => $productName,
                        'category' => $category,
                        'total_quantity' => 0,
                        'last_updated' => null
                    ]);
                }
            }
        }

        $supplyCenters = SupplyCenter::all();
        $rawCoffeeItems = RawCoffee::all();
        $coffeeProductItems = CoffeeProduct::all();
        
        
    
// Calculate quantities for raw coffee types
$rawCoffeeArabicaQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
    ->where('raw_coffee.coffee_type', 'Arabica')
    ->sum('inventory.quantity_in_stock');
    
$rawCoffeeRobustaQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
    ->where('raw_coffee.coffee_type', 'Robusta')
    ->sum('inventory.quantity_in_stock');

// Calculate quantities for processed coffee products
$processedCoffeeMountainBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
    ->where('coffee_product.name', 'Mountain Blend')
    ->sum('inventory.quantity_in_stock');
    
$processedCoffeeMorningBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
    ->where('coffee_product.name', 'Morning Brew')
    ->sum('inventory.quantity_in_stock');

// Calculate percentage changes
$arabicaChange = $this->calculatePercentageChange('Arabica');
$robustaChange = $this->calculatePercentageChange('Robusta');
$mountainBrewChange = $this->calculatePercentageChange('Mountain Blend');
$morningBrewChange = $this->calculatePercentageChange('Morning Brew');

return view('Inventory.inventory', compact(
    'rawCoffeeInventory', 
    'coffeeProductInventory', 
    'supplyCenters', 
    'rawCoffeeItems', 
    'coffeeProductItems',
    'rawCoffeeArabicaQuantity',
    'rawCoffeeRobustaQuantity',
    'processedCoffeeMountainBrewQuantity',
    'processedCoffeeMorningBrewQuantity',
    'arabicaChange',
    'robustaChange',
    'mountainBrewChange',
    'morningBrewChange'
));
    }

    /**
     * Get detailed information for a specific item
     */
    public function getItemDetails($type, $id)
    {
        $grade = request()->query('grade');
        $category = request()->query('category');
        
        if ($type === 'raw-coffee') {
            if ($id === 'placeholder' && $grade) {
                // Handle placeholder case - find the actual raw coffee record
                $rawCoffee = RawCoffee::where('grade', $grade)->first();
                if (!$rawCoffee) {
                    return response()->json([
                        'type' => 'raw-coffee',
                        'id' => null,
                        'name' => 'Unknown',
                        'grade' => $grade,
                        'total_quantity' => 0,
                        'inventory_details' => []
                    ]);
                }
            } else {
                // Get the raw coffee record
                $rawCoffee = RawCoffee::findOrFail($id);
            }
            
            if ($grade) {
                // Get specific grade for this coffee type
                $specificRawCoffee = RawCoffee::where('coffee_type', $rawCoffee->coffee_type)
                    ->where('grade', $grade)
                    ->first();
                
                if ($specificRawCoffee) {
                    $inventories = Inventory::where('raw_coffee_id', $specificRawCoffee->id)
                        ->with('supplyCenter')
                        ->get();
                    
                    $inventoryDetails = $inventories->map(function($inv) use ($grade) {
                        return [
                            'supply_center' => $inv->supplyCenter->name,
                            'quantity' => $inv->quantity_in_stock,
                            'last_updated' => $inv->updated_at->format('Y-m-d H:i:s'),
                            'grade' => $grade
                        ];
                    });
                    
                    return response()->json([
                        'type' => 'raw-coffee',
                        'id' => $specificRawCoffee->id,
                        'name' => $rawCoffee->coffee_type,
                        'grade' => $grade,
                        'total_quantity' => $inventories->sum('quantity_in_stock'),
                        'inventory_details' => $inventoryDetails->toArray()
                    ]);
                } else {
                    // No specific grade found, return empty data
                    return response()->json([
                        'type' => 'raw-coffee',
                        'id' => null,
                        'name' => $rawCoffee->coffee_type,
                        'grade' => $grade,
                        'total_quantity' => 0,
                        'inventory_details' => []
                    ]);
                }
            }
            
            // Fallback: Get all raw coffee records with the same coffee type
            $allRawCoffee = RawCoffee::where('coffee_type', $rawCoffee->coffee_type)->get();
            
            // Collect inventory details for all grades of this coffee type
            $inventoryDetails = collect();
            foreach ($allRawCoffee as $rc) {
                $inventories = Inventory::where('raw_coffee_id', $rc->id)
                    ->with('supplyCenter')
                    ->get();
                
                foreach ($inventories as $inv) {
                    $inventoryDetails->push([
                        'supply_center' => $inv->supplyCenter->name,
                        'quantity' => $inv->quantity_in_stock,
                        'last_updated' => $inv->updated_at->format('Y-m-d H:i:s'),
                        'grade' => $rc->grade
                    ]);
                }
            }
            
            return response()->json([
                'type' => 'raw-coffee',
                'id' => $rawCoffee->id,
                'name' => $rawCoffee->coffee_type,
                'grade' => 'All Grades',
                'total_quantity' => $inventoryDetails->sum('quantity'),
                'inventory_details' => $inventoryDetails->toArray()
            ]);
        } else {
            if ($id === 'placeholder' && $category) {
                // Handle placeholder case - find the actual coffee product record
                $coffeeProduct = CoffeeProduct::where('category', $category)->first();
                if (!$coffeeProduct) {
                    return response()->json([
                        'type' => 'coffee-product',
                        'id' => null,
                        'name' => 'Unknown',
                        'category' => $category,
                        'total_quantity' => 0,
                        'inventory_details' => []
                    ]);
                }
            } else {
                // Get the coffee product record
                $coffeeProduct = CoffeeProduct::findOrFail($id);
            }
            
            if ($category) {
                // Get specific category for this product name
                $specificCoffeeProduct = CoffeeProduct::where('name', $coffeeProduct->name)
                    ->where('category', $category)
                    ->first();
                
                if ($specificCoffeeProduct) {
                    $inventories = Inventory::where('coffee_product_id', $specificCoffeeProduct->id)
                        ->with('supplyCenter')
                        ->get();
                    
                    $inventoryDetails = $inventories->map(function($inv) use ($category) {
                        return [
                            'supply_center' => $inv->supplyCenter->name,
                            'quantity' => $inv->quantity_in_stock,
                            'last_updated' => $inv->updated_at->format('Y-m-d H:i:s'),
                            'category' => $category
                        ];
                    });
                    
                    return response()->json([
                        'type' => 'coffee-product',
                        'id' => $specificCoffeeProduct->id,
                        'name' => $coffeeProduct->name,
                        'category' => $category,
                        'total_quantity' => $inventories->sum('quantity_in_stock'),
                        'inventory_details' => $inventoryDetails->toArray()
                    ]);
                } else {
                    // No specific category found, return empty data
                    return response()->json([
                        'type' => 'coffee-product',
                        'id' => null,
                        'name' => $coffeeProduct->name,
                        'category' => $category,
                        'total_quantity' => 0,
                        'inventory_details' => []
                    ]);
                }
            }
            
            // Fallback: Get all coffee product records with the same name
            $allCoffeeProducts = CoffeeProduct::where('name', $coffeeProduct->name)->get();
            
            // Collect inventory details for all categories of this product
            $inventoryDetails = collect();
            foreach ($allCoffeeProducts as $cp) {
                $inventories = Inventory::where('coffee_product_id', $cp->id)
                    ->with('supplyCenter')
                    ->get();
                
                foreach ($inventories as $inv) {
                    $inventoryDetails->push([
                        'supply_center' => $inv->supplyCenter->name,
                        'quantity' => $inv->quantity_in_stock,
                        'last_updated' => $inv->updated_at->format('Y-m-d H:i:s'),
                        'category' => $cp->category
                    ]);
                }
            }
            
            return response()->json([
                'type' => 'coffee-product',
                'id' => $coffeeProduct->id,
                'name' => $coffeeProduct->name,
                'category' => 'All Categories',
                'total_quantity' => $inventoryDetails->sum('quantity'),
                'inventory_details' => $inventoryDetails->toArray()
            ]);
        }
    }

    /**
     * Calculate percentage change from previous week
     */
    private function calculatePercentageChange($type)
    {
        $currentWeek = now()->startOfWeek();
        $previousWeek = now()->subWeek()->startOfWeek();
        
        if (in_array($type, ['Arabica', 'Robusta'])) {
            // For raw coffee types
            $currentQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->where('raw_coffee.coffee_type', $type)
                ->where('inventory.updated_at', '>=', $currentWeek)
                ->sum('inventory.quantity_in_stock');
                
            $previousQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->where('raw_coffee.coffee_type', $type)
                ->whereBetween('inventory.updated_at', [$previousWeek, $currentWeek])
                ->sum('inventory.quantity_in_stock');
        } else {
            // For processed coffee products
            $currentQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
                ->where('coffee_product.name', $type)
                ->where('inventory.updated_at', '>=', $currentWeek)
                ->sum('inventory.quantity_in_stock');
                
            $previousQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
                ->where('coffee_product.name', $type)
                ->whereBetween('inventory.updated_at', [$previousWeek, $currentWeek])
                ->sum('inventory.quantity_in_stock');
        }
        
        if ($previousQuantity == 0) {
            return $currentQuantity > 0 ? 100 : 0;
        }
        
        $change = (($currentQuantity - $previousQuantity) / $previousQuantity) * 100;
        return round($change, 1);
    }

    // Store a new inventory item
    public function store(Request $request)
    {
        $request->validate([
            'supply_center_id' => 'required|exists:supply_centers,id',
            'quantity_in_stock' => 'required|numeric|min:0',
            'raw_coffee_id' => 'nullable|exists:raw_coffee,id',
            'coffee_product_id' => 'nullable|exists:coffee_product,id',
            'grade' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
        ]);

        // Ensure either raw_coffee_id or coffee_product_id is provided, but not both
        if (empty($request->raw_coffee_id) && empty($request->coffee_product_id)) {
            return redirect()->route('inventory.index')->with('error', 'Please select either a raw coffee item or a processed coffee product.');
        }

        if (!empty($request->raw_coffee_id) && !empty($request->coffee_product_id)) {
            return redirect()->route('inventory.index')->with('error', 'Please select either a raw coffee item or a processed coffee product, not both.');
        }

        $data = $request->all();
        
        // Handle quantity field mapping if needed
        if (isset($data['quantity'])) {
            $data['quantity_in_stock'] = $data['quantity'];
            unset($data['quantity']);
        }

        try {
            // Handle raw coffee inventory
            if (isset($data['raw_coffee_id']) && !empty($data['raw_coffee_id'])) {
                $rawCoffee = RawCoffee::find($data['raw_coffee_id']);
                
                if (!$rawCoffee) {
                    return redirect()->route('inventory.index')->with('error', 'Selected raw coffee item not found.');
                }
                
                // If grade is provided and different from existing, create new raw coffee record
                if (isset($data['grade']) && $rawCoffee && $rawCoffee->grade !== $data['grade']) {
                    // Create new raw coffee record with the specified grade
                    $newRawCoffee = RawCoffee::create([
                        'supplier_id' => $rawCoffee->supplier_id,
                        'coffee_type' => $rawCoffee->coffee_type,
                        'grade' => $data['grade'],
                        'screen_size' => $rawCoffee->screen_size,
                        'defect_count' => $rawCoffee->defect_count,
                        'harvest_date' => $rawCoffee->harvest_date,
                    ]);
                    $data['raw_coffee_id'] = $newRawCoffee->id;
                } elseif (isset($data['grade'])) {
                    // Update the existing raw coffee record with the new grade
                    $rawCoffee->update(['grade' => $data['grade']]);
                }
                
                // Remove grade from data as it's not part of inventory table
                unset($data['grade']);
                
                // Ensure coffee_product_id is null for raw coffee
                $data['coffee_product_id'] = null;
            }
            
            // Handle coffee product inventory
            if (isset($data['coffee_product_id']) && !empty($data['coffee_product_id'])) {
                $coffeeProduct = CoffeeProduct::find($data['coffee_product_id']);
                
                if (!$coffeeProduct) {
                    return redirect()->route('inventory.index')->with('error', 'Selected coffee product not found.');
                }
                
                // If category is provided and different from existing, create new coffee product record
                if (isset($data['category']) && $coffeeProduct && $coffeeProduct->category !== $data['category']) {
                    // Create new coffee product record with the specified category
                    $newCoffeeProduct = CoffeeProduct::create([
                        'raw_coffee_id' => $coffeeProduct->raw_coffee_id,
                        'name' => $coffeeProduct->name,
                        'category' => $data['category'],
                        'product_form' => $coffeeProduct->product_form,
                        'roast_level' => $coffeeProduct->roast_level,
                        'production_date' => $coffeeProduct->production_date,
                    ]);
                    $data['coffee_product_id'] = $newCoffeeProduct->id;
                } elseif (isset($data['category'])) {
                    // Update the existing coffee product record with the new category
                    $coffeeProduct->update(['category' => $data['category']]);
                }
                
                // Remove category from data as it's not part of inventory table
                unset($data['category']);
                
                // Ensure raw_coffee_id is null for coffee product
                $data['raw_coffee_id'] = null;
            }

            // Ensure warehouse_id is null since we're using supply_center_id
            $data['warehouse_id'] = null;

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
            // Find the inventory record for this raw coffee and supply center
            $inventory = Inventory::where('raw_coffee_id', $rawCoffeeId)
                ->where('supply_center_id', $request->supply_center_id)
                ->first();
            
            if (!$inventory) {
                // Create new inventory record if it doesn't exist
                $inventory = Inventory::create([
                    'raw_coffee_id' => $rawCoffeeId,
                    'coffee_product_id' => null,
                    'quantity_in_stock' => $request->quantity_in_stock,
                    'supply_center_id' => $request->supply_center_id,
                ]);
            } else {
                // Update existing inventory record
                $inventory->update([
                    'quantity_in_stock' => $request->quantity_in_stock,
                    'supply_center_id' => $request->supply_center_id,
                ]);
            }
            
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
            // Find the inventory record for this coffee product and supply center
            $inventory = Inventory::where('coffee_product_id', $coffeeProductId)
                ->where('supply_center_id', $request->supply_center_id)
                ->first();
            
            if (!$inventory) {
                // Create new inventory record if it doesn't exist
                $inventory = Inventory::create([
                    'raw_coffee_id' => null,
                    'coffee_product_id' => $coffeeProductId,
                    'quantity_in_stock' => $request->quantity_in_stock,
                    'supply_center_id' => $request->supply_center_id,
                ]);
            } else {
                // Update existing inventory record
                $inventory->update([
                    'quantity_in_stock' => $request->quantity_in_stock,
                    'supply_center_id' => $request->supply_center_id,
                ]);
            }
            
            return redirect()->route('inventory.index')->with('success', 'Coffee product updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to update item: ' . $e->getMessage());
        }
    }

    // Delete Raw Coffee inventory
    public function destroyRawCoffee($rawCoffeeId)
    {
        try {
            // Delete all inventory records for this raw coffee
            $inventories = Inventory::where('raw_coffee_id', $rawCoffeeId)->get();
            
            if ($inventories->count() > 0) {
                foreach ($inventories as $inventory) {
                    $inventory->delete();
                }
                return redirect()->route('inventory.index')->with('success', 'Raw coffee item and all its inventory records deleted successfully!');
            } else {
                return redirect()->route('inventory.index')->with('error', 'No inventory records found for this item.');
            }
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    // Delete Coffee Product inventory
    public function destroyCoffeeProduct($coffeeProductId)
    {
        try {
            // Delete all inventory records for this coffee product
            $inventories = Inventory::where('coffee_product_id', $coffeeProductId)->get();
            
            if ($inventories->count() > 0) {
                foreach ($inventories as $inventory) {
                    $inventory->delete();
                }
                return redirect()->route('inventory.index')->with('success', 'Coffee product and all its inventory records deleted successfully!');
            } else {
                return redirect()->route('inventory.index')->with('error', 'No inventory records found for this item.');
            }
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

    /**
     * Fetch real-time inventory statistics for the dashboard cards.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function stats()
    {
        // Calculate quantities for raw coffee types
        $rawCoffeeArabicaQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
            ->where('raw_coffee.coffee_type', 'Arabica')
            ->sum('inventory.quantity_in_stock');
            
        $rawCoffeeRobustaQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
            ->where('raw_coffee.coffee_type', 'Robusta')
            ->sum('inventory.quantity_in_stock');
        
        // Calculate quantities for processed coffee products
        $processedCoffeeMountainBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
            ->where('coffee_product.name', 'Mountain Blend')
            ->sum('inventory.quantity_in_stock');
            
        $processedCoffeeMorningBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
            ->where('coffee_product.name', 'Morning Brew')
            ->sum('inventory.quantity_in_stock');

        // Calculate percentage changes
        $arabicaChange = $this->calculatePercentageChange('Arabica');
        $robustaChange = $this->calculatePercentageChange('Robusta');
        $mountainBrewChange = $this->calculatePercentageChange('Mountain Blend');
        $morningBrewChange = $this->calculatePercentageChange('Morning Brew');

        return response()->json([
            'rawCoffeeArabicaQuantity' => number_format($rawCoffeeArabicaQuantity),
            'rawCoffeeRobustaQuantity' => number_format($rawCoffeeRobustaQuantity),
            'processedCoffeeMountainBrewQuantity' => number_format($processedCoffeeMountainBrewQuantity),
            'processedCoffeeMorningBrewQuantity' => number_format($processedCoffeeMorningBrewQuantity),
            'arabicaChange' => $arabicaChange,
            'robustaChange' => $robustaChange,
            'mountainBrewChange' => $mountainBrewChange,
            'morningBrewChange' => $morningBrewChange
        ]);
    }
}
