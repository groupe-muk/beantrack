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
                    ->whereNotNull('supply_center_id') // Only admin supply centers
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
                        ->whereNotNull('supply_center_id') // Only admin supply centers
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
        
        
    
// Calculate quantities for raw coffee types (admin supply centers only)
$rawCoffeeArabicaQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
    ->where('raw_coffee.coffee_type', 'Arabica')
    ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
    ->sum('inventory.quantity_in_stock');
    
$rawCoffeeRobustaQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
    ->where('raw_coffee.coffee_type', 'Robusta')
    ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
    ->sum('inventory.quantity_in_stock');

// Calculate quantities for processed coffee products (admin supply centers only)
$processedCoffeeMountainBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
    ->where('coffee_product.name', 'Mountain Blend')
    ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
    ->sum('inventory.quantity_in_stock');
    
$processedCoffeeMorningBrewQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
    ->where('coffee_product.name', 'Morning Brew')
    ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
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
        $showAllVariants = request()->query('show_all_variants', false);
        
        if ($type === 'raw-coffee') {
            $rawCoffee = RawCoffee::findOrFail($id);
            
            if ($showAllVariants) {
                // Get all grades available for this coffee type
                $allGrades = RawCoffee::where('coffee_type', $rawCoffee->coffee_type)
                    ->distinct()
                    ->pluck('grade')
                    ->toArray();
                
                $variantDetails = [];
                foreach ($allGrades as $gradeVariant) {
                    $specificRawCoffee = RawCoffee::where('coffee_type', $rawCoffee->coffee_type)
                        ->where('grade', $gradeVariant)
                        ->first();
                    
                    if ($specificRawCoffee) {
                        $inventories = Inventory::where('raw_coffee_id', $specificRawCoffee->id)
                            ->whereNotNull('supply_center_id')
                            ->with('supplyCenter')
                            ->get();
                        
                        $inventoryDetails = $inventories->map(function($inv) use ($gradeVariant) {
                            return [
                                'id' => $inv->id,
                                'supply_center' => $inv->supplyCenter ? $inv->supplyCenter->name : 'Unknown Supply Center',
                                'quantity' => $inv->quantity_in_stock,
                                'last_updated' => $inv->updated_at->format('Y-m-d H:i:s'),
                                'grade' => $gradeVariant
                            ];
                        });
                        
                        $variantDetails[] = [
                            'grade' => $gradeVariant,
                            'total_quantity' => $inventories->sum('quantity_in_stock'),
                            'inventory_details' => $inventoryDetails->toArray()
                        ];
                    }
                }
                
                return response()->json([
                    'type' => 'raw-coffee',
                    'id' => $rawCoffee->id,
                    'name' => $rawCoffee->coffee_type,
                    'show_all_variants' => true,
                    'variants' => $variantDetails
                ]);
            }
            
            if ($grade) {
                // Get specific grade for this coffee type
                $specificRawCoffee = RawCoffee::where('coffee_type', $rawCoffee->coffee_type)
                    ->where('grade', $grade)
                    ->first();
                
                if ($specificRawCoffee) {
                    $inventories = Inventory::where('raw_coffee_id', $specificRawCoffee->id)
                        ->whereNotNull('supply_center_id') // Only admin supply centers
                        ->with('supplyCenter')
                        ->get();
                    
                    $inventoryDetails = $inventories->map(function($inv) use ($grade) {
                        return [
                            'id' => $inv->id,
                            'supply_center' => $inv->supplyCenter ? $inv->supplyCenter->name : 'Unknown Supply Center',
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
                    ->whereNotNull('supply_center_id') // Only admin supply centers
                    ->with('supplyCenter')
                    ->get();
                
                foreach ($inventories as $inv) {
                    $inventoryDetails->push([
                        'supply_center' => $inv->supplyCenter ? $inv->supplyCenter->name : 'Unknown Supply Center',
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
            // Coffee Product
            $coffeeProduct = CoffeeProduct::findOrFail($id);
            
            if ($showAllVariants) {
                // Get all categories available for this product name
                $allCategories = CoffeeProduct::where('name', $coffeeProduct->name)
                    ->distinct()
                    ->pluck('category')
                    ->toArray();
                
                $variantDetails = [];
                foreach ($allCategories as $categoryVariant) {
                    $specificCoffeeProduct = CoffeeProduct::where('name', $coffeeProduct->name)
                        ->where('category', $categoryVariant)
                        ->first();
                    
                    if ($specificCoffeeProduct) {
                        $inventories = Inventory::where('coffee_product_id', $specificCoffeeProduct->id)
                            ->whereNotNull('supply_center_id')
                            ->with('supplyCenter')
                            ->get();
                        
                        $inventoryDetails = $inventories->map(function($inv) use ($categoryVariant) {
                            return [
                                'id' => $inv->id,
                                'supply_center' => $inv->supplyCenter ? $inv->supplyCenter->name : 'Unknown Supply Center',
                                'quantity' => $inv->quantity_in_stock,
                                'last_updated' => $inv->updated_at->format('Y-m-d H:i:s'),
                                'category' => $categoryVariant
                            ];
                        });
                        
                        $variantDetails[] = [
                            'category' => $categoryVariant,
                            'total_quantity' => $inventories->sum('quantity_in_stock'),
                            'inventory_details' => $inventoryDetails->toArray()
                        ];
                    }
                }
                
                return response()->json([
                    'type' => 'coffee-product',
                    'id' => $coffeeProduct->id,
                    'name' => $coffeeProduct->name,
                    'show_all_variants' => true,
                    'variants' => $variantDetails
                ]);
            }
            
            if ($category) {
                // Get specific category for this product name
                $specificCoffeeProduct = CoffeeProduct::where('name', $coffeeProduct->name)
                    ->where('category', $category)
                    ->first();
                
                if ($specificCoffeeProduct) {
                    $inventories = Inventory::where('coffee_product_id', $specificCoffeeProduct->id)
                        ->whereNotNull('supply_center_id') // Only admin supply centers
                        ->with('supplyCenter')
                        ->get();
                    
                    $inventoryDetails = $inventories->map(function($inv) use ($category) {
                        return [
                            'id' => $inv->id,
                            'supply_center' => $inv->supplyCenter ? $inv->supplyCenter->name : 'Unknown Supply Center',
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
                    ->whereNotNull('supply_center_id') // Only admin supply centers
                    ->with('supplyCenter')
                    ->get();
                
                foreach ($inventories as $inv) {
                    $inventoryDetails->push([
                        'supply_center' => $inv->supplyCenter ? $inv->supplyCenter->name : 'Unknown Supply Center',
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
            // For raw coffee types (admin supply centers only)
            $currentQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->where('raw_coffee.coffee_type', $type)
                ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
                ->where('inventory.updated_at', '>=', $currentWeek)
                ->sum('inventory.quantity_in_stock');
                
            $previousQuantity = Inventory::join('raw_coffee', 'inventory.raw_coffee_id', '=', 'raw_coffee.id')
                ->where('raw_coffee.coffee_type', $type)
                ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
                ->whereBetween('inventory.updated_at', [$previousWeek, $currentWeek])
                ->sum('inventory.quantity_in_stock');
        } else {
            // For processed coffee products (admin supply centers only)
            $currentQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
                ->where('coffee_product.name', $type)
                ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
                ->where('inventory.updated_at', '>=', $currentWeek)
                ->sum('inventory.quantity_in_stock');
                
            $previousQuantity = Inventory::join('coffee_product', 'inventory.coffee_product_id', '=', 'coffee_product.id')
                ->where('coffee_product.name', $type)
                ->whereNotNull('inventory.supply_center_id') // Only admin supply centers
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
            'defect_count' => 'nullable|integer|min:0',
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
            // Explicitly set one ID to null based on which type we're working with
            if (!empty($data['raw_coffee_id'])) {
                $data['coffee_product_id'] = null;
                
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
                        'defect_count' => isset($data['defect_count']) && $data['defect_count'] !== null ? $data['defect_count'] : $rawCoffee->defect_count,
                        'harvest_date' => $rawCoffee->harvest_date,
                    ]);
                    $data['raw_coffee_id'] = $newRawCoffee->id;
                } elseif (isset($data['defect_count']) && $data['defect_count'] !== null) {
                    // Update defect count on existing raw coffee if only defect count changed
                    $rawCoffee->update(['defect_count' => $data['defect_count']]);
                }
                
                // Remove grade and defect_count from data as they're not part of inventory table
                unset($data['grade']);
                unset($data['defect_count']);
                
            } elseif (!empty($data['coffee_product_id'])) {
                $data['raw_coffee_id'] = null;
                
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
            }

            // Ensure warehouse_id is null since we're using supply_center_id
            $data['warehouse_id'] = null;

            // Clean up any extra fields that shouldn't be in the inventory table
            $allowedFields = ['raw_coffee_id', 'coffee_product_id', 'supply_center_id', 'warehouse_id', 'quantity_in_stock'];
            $cleanData = array_intersect_key($data, array_flip($allowedFields));

            Inventory::create($cleanData);
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

    /**
     * Create a new raw coffee type (Admin only) - REMOVED
     * Raw coffee items should only be created by suppliers
     */
    public function createRawCoffee(Request $request)
    {
        return redirect()->route('inventory.index')
            ->with('error', 'Raw coffee items can only be created by suppliers. Please contact the relevant supplier to add new coffee types.');
    }

        /**
     * Create a new coffee product (Admin only)
     */
    public function createCoffeeProduct(Request $request)
    {
        $validated = $request->validate([
            'raw_coffee_id' => 'required|exists:raw_coffee,id',
            'name' => 'required|string|max:100|unique:coffee_product,name',
            'categories' => 'required|array|min:1',
            'categories.*' => 'string|in:premium,standard,specialty,organic',
            'product_form' => 'required|string|max:50',
            'roast_level' => 'nullable|string|max:20',
        ]);

        // Create a coffee product record for each selected category
        $createdProducts = [];
        foreach ($validated['categories'] as $category) {
            // Check if this combination already exists
            $existingProduct = CoffeeProduct::where('name', $validated['name'])
                ->where('category', $category)
                ->first();
                
            if (!$existingProduct) {
                $product = CoffeeProduct::create([
                    'raw_coffee_id' => $validated['raw_coffee_id'],
                    'name' => $validated['name'],
                    'category' => $category,
                    'product_form' => $validated['product_form'],
                    'roast_level' => $validated['roast_level'],
                    'production_date' => null, // Will be set when inventory batches are produced
                ]);
                $createdProducts[] = ucfirst($category);
            }
        }

        $message = count($createdProducts) > 0 
            ? 'New coffee product created successfully for categories: ' . implode(', ', $createdProducts)
            : 'Coffee product creation completed (some categories may already exist)';

        return redirect()->route('inventory.index')
            ->with('success', $message);
    }

    /**
     * Get individual inventory item for editing
     */
    public function getItem($id)
    {
        $inventoryItem = Inventory::with(['rawCoffee', 'coffeeProduct', 'supplyCenter'])
            ->whereNotNull('supply_center_id') // Only admin supply centers
            ->findOrFail($id);

        return response()->json([
            'item' => $inventoryItem,
            'success' => true
        ]);
    }
    
    /**
     * Get details by coffee type (matching supplier inventory pattern)
     */
    public function getDetailsByType($coffeeType)
    {
        try {
            // Get all raw coffee records for this coffee type
            $rawCoffees = RawCoffee::where('coffee_type', $coffeeType)->get();
            
            if ($rawCoffees->isEmpty()) {
                return response()->json([
                    'name' => $coffeeType,
                    'total_quantity' => 0,
                    'breakdown' => []
                ]);
            }
            
            $totalQuantity = 0;
            $gradeBreakdown = [];
            
            // Group by grade
            $gradeGroups = $rawCoffees->groupBy('grade');
            
            foreach ($gradeGroups as $grade => $coffees) {
                $gradeQuantity = 0;
                $gradeDetails = [];
                
                foreach ($coffees as $coffee) {
                    $inventories = Inventory::where('raw_coffee_id', $coffee->id)
                        ->whereNotNull('supply_center_id')
                        ->with('supplyCenter')
                        ->get();
                    
                    foreach ($inventories as $inventory) {
                        $gradeQuantity += $inventory->quantity_in_stock;
                        $gradeDetails[] = [
                            'id' => $inventory->id,
                            'location' => $inventory->supplyCenter ? $inventory->supplyCenter->name : 'Unknown',
                            'quantity' => $inventory->quantity_in_stock,
                            'last_updated' => $inventory->updated_at->format('Y-m-d H:i:s')
                        ];
                    }
                }
                
                $totalQuantity += $gradeQuantity;
                $gradeBreakdown[] = [
                    'grade' => $grade ?: 'Unknown',
                    'total_quantity' => $gradeQuantity,
                    'details' => $gradeDetails
                ];
            }
            
            return response()->json([
                'name' => $coffeeType,
                'total_quantity' => $totalQuantity,
                'breakdown' => $gradeBreakdown
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting coffee type details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load details'], 500);
        }
    }
    
    /**
     * Get details by product name (matching supplier inventory pattern)
     */
    public function getProductDetailsByName($productName)
    {
        try {
            // Get all coffee product records for this product name
            $coffeeProducts = CoffeeProduct::where('name', $productName)->get();
            
            if ($coffeeProducts->isEmpty()) {
                return response()->json([
                    'name' => $productName,
                    'total_quantity' => 0,
                    'breakdown' => []
                ]);
            }
            
            $totalQuantity = 0;
            $categoryBreakdown = [];
            
            // Group by category
            $categoryGroups = $coffeeProducts->groupBy('category');
            
            foreach ($categoryGroups as $category => $products) {
                $categoryQuantity = 0;
                $categoryDetails = [];
                
                foreach ($products as $product) {
                    $inventories = Inventory::where('coffee_product_id', $product->id)
                        ->whereNotNull('supply_center_id')
                        ->with('supplyCenter')
                        ->get();
                    
                    foreach ($inventories as $inventory) {
                        $categoryQuantity += $inventory->quantity_in_stock;
                        $categoryDetails[] = [
                            'id' => $inventory->id,
                            'location' => $inventory->supplyCenter ? $inventory->supplyCenter->name : 'Unknown',
                            'quantity' => $inventory->quantity_in_stock,
                            'last_updated' => $inventory->updated_at->format('Y-m-d H:i:s')
                        ];
                    }
                }
                
                $totalQuantity += $categoryQuantity;
                $categoryBreakdown[] = [
                    'location' => $category ?: 'Unknown',
                    'total_quantity' => $categoryQuantity,
                    'details' => $categoryDetails
                ];
            }
            
            return response()->json([
                'name' => $productName,
                'total_quantity' => $totalQuantity,
                'breakdown' => $categoryBreakdown
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error getting product details: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load details'], 500);
        }
    }

    /**
     * Show the form for editing an individual inventory item
     */
    public function editItem($id)
    {
        try {
            $inventory = Inventory::with(['rawCoffee', 'coffeeProduct', 'supplyCenter'])->findOrFail($id);
            
            return view('admin.inventory.edit', compact('inventory'));
        } catch (\Exception $e) {
            return redirect()->route('inventory.index')->with('error', 'Inventory item not found.');
        }
    }

    /**
     * Update an individual inventory item
     */
    public function updateItem(Request $request, $id)
    {
        try {
            $request->validate([
                'quantity_in_stock' => 'required|numeric|min:0',
                'storage_location' => 'nullable|string|max:255',
            ]);

            $inventory = Inventory::findOrFail($id);
            $oldQuantity = $inventory->quantity_in_stock;
            
            $inventory->update([
                'quantity_in_stock' => $request->quantity_in_stock,
                'storage_location' => $request->storage_location,
            ]);

            // Create inventory update record to track the change
            \App\Models\InventoryUpdate::create([
                'inventory_id' => $inventory->id,
                'quantity_change' => $request->quantity_in_stock - $oldQuantity,
                'type' => 'manual_adjustment',
                'notes' => 'Admin manual update',
            ]);

            if ($request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Inventory item updated successfully.']);
            }

            return redirect()->route('inventory.index')->with('success', 'Inventory item updated successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update inventory item.'], 500);
            }
            return redirect()->route('inventory.index')->with('error', 'Failed to update inventory item.');
        }
    }

    /**
     * Delete an individual inventory item
     */
    public function deleteItem($id)
    {
        try {
            $inventory = Inventory::whereNotNull('supply_center_id') // Only admin inventory
                ->findOrFail($id);
            
            // Simply delete the inventory record
            // The database should handle the cascade delete for inventory_updates if configured
            $inventory->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Inventory item deleted successfully.']);
            }

            return redirect()->route('inventory.index')->with('success', 'Inventory item deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Inventory item not found.'], 404);
            }
            return redirect()->route('inventory.index')->with('error', 'Inventory item not found.');
        } catch (\Exception $e) {
            \Log::error('Error deleting inventory item: ' . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete inventory item: ' . $e->getMessage()], 500);
            }
            return redirect()->route('inventory.index')->with('error', 'Failed to delete inventory item.');
        }
    }
}
