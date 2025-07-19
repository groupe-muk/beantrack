<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'raw_coffee_id',
        'coffee_product_id',
        'category',
        'quantity_in_stock',
        'supply_center_id',
        'warehouse_id',
        'storage_location',
        'last_updated'
    ];

    public function rawCoffee()
    {
        return $this->belongsTo(RawCoffee::class, 'raw_coffee_id');
    }

    public function coffeeProduct()
    {
        return $this->belongsTo(CoffeeProduct::class, 'coffee_product_id');
    }

    public function supplyCenter()
    {
        return $this->belongsTo(SupplyCenter::class, 'supply_center_id');
    }

    public function inventoryUpdates()
    {
        return $this->hasMany(InventoryUpdate::class, 'inventory_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Check total available stock for a raw coffee across all warehouses
     */
    public static function getAvailableStock($rawCoffeeId)
    {
        return self::where('raw_coffee_id', $rawCoffeeId)
            ->where('quantity_in_stock', '>', 0)
            ->sum('quantity_in_stock');
    }

    /**
     * Check total available stock for a raw coffee in supplier's warehouses only
     */
    public static function getAvailableStockForSupplier($rawCoffeeId, $supplierId)
    {
        $warehouseIds = \App\Models\Warehouse::where('supplier_id', $supplierId)->pluck('id');
        
        return self::where('raw_coffee_id', $rawCoffeeId)
            ->whereIn('warehouse_id', $warehouseIds)
            ->where('quantity_in_stock', '>', 0)
            ->sum('quantity_in_stock');
    }

    /**
     * Check if sufficient stock is available for an order
     */
    public static function hasSufficientStock($rawCoffeeId, $requiredQuantity)
    {
        $availableStock = self::getAvailableStock($rawCoffeeId);
        return $availableStock >= $requiredQuantity;
    }

    /**
     * Reduce inventory stock for an order (FIFO - First In, First Out)
     */
    public static function reduceStock($rawCoffeeId, $quantityToReduce, $orderId = null, $userId = null)
    {
        $inventories = self::where('raw_coffee_id', $rawCoffeeId)
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO - oldest stock first
            ->get();

        $remainingToReduce = $quantityToReduce;
        $reductions = [];

        foreach ($inventories as $inventory) {
            if ($remainingToReduce <= 0) {
                break;
            }

            $availableInThisInventory = $inventory->quantity_in_stock;
            $reductionFromThis = min($remainingToReduce, $availableInThisInventory);

            if ($reductionFromThis > 0) {
                $newStock = $inventory->quantity_in_stock - $reductionFromThis;
                
                $inventory->update([
                    'quantity_in_stock' => $newStock,
                    'last_updated' => now()
                ]);

                // Create inventory update record
                InventoryUpdate::create([
                    'inventory_id' => $inventory->id,
                    'quantity_change' => -$reductionFromThis, // Negative for reduction
                    'reason' => $orderId ? "Order fulfillment - Order #{$orderId}" : 'Stock reduction',
                    'updated_by' => $userId,
                    'created_at' => now()
                ]);

                $reductions[] = [
                    'inventory_id' => $inventory->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'reduced_quantity' => $reductionFromThis,
                    'remaining_stock' => $newStock
                ];

                $remainingToReduce -= $reductionFromThis;
            }
        }

        return $reductions;
    }

    /**
     * Reduce inventory stock for supplier warehouses only (FIFO - First In, First Out)
     */
    public static function reduceStockForSupplier($rawCoffeeId, $quantityToReduce, $supplierId, $orderId = null, $userId = null)
    {
        $warehouseIds = \App\Models\Warehouse::where('supplier_id', $supplierId)->pluck('id');
        
        $inventories = self::where('raw_coffee_id', $rawCoffeeId)
            ->whereIn('warehouse_id', $warehouseIds)
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO - oldest stock first
            ->get();

        $remainingToReduce = $quantityToReduce;
        $reductions = [];

        foreach ($inventories as $inventory) {
            if ($remainingToReduce <= 0) {
                break;
            }

            $availableInThisInventory = $inventory->quantity_in_stock;
            $reductionFromThis = min($remainingToReduce, $availableInThisInventory);

            if ($reductionFromThis > 0) {
                $newStock = $inventory->quantity_in_stock - $reductionFromThis;
                
                $inventory->update([
                    'quantity_in_stock' => $newStock,
                    'last_updated' => now()
                ]);

                // Create inventory update record
                InventoryUpdate::create([
                    'inventory_id' => $inventory->id,
                    'quantity_change' => -$reductionFromThis, // Negative for reduction
                    'reason' => $orderId ? "Supplier order fulfillment - Order #{$orderId}" : 'Supplier stock reduction',
                    'updated_by' => $userId,
                    'created_at' => now()
                ]);

                $reductions[] = [
                    'inventory_id' => $inventory->id,
                    'warehouse_id' => $inventory->warehouse_id,
                    'reduced_quantity' => $reductionFromThis,
                    'remaining_stock' => $newStock
                ];

                $remainingToReduce -= $reductionFromThis;
            }
        }

        return $reductions;
    }

    /**
     * Restore inventory stock (e.g., when an order is cancelled after being confirmed)
     */
    public static function restoreStock($rawCoffeeId, $quantityToRestore, $orderId = null, $userId = null)
    {
        // Try to find the most recent inventory record for this raw coffee
        $inventory = self::where('raw_coffee_id', $rawCoffeeId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$inventory) {
            // If no inventory record exists, we can't restore - this would be an error case
            Log::error('Cannot restore stock - no inventory record found', [
                'raw_coffee_id' => $rawCoffeeId,
                'quantity_to_restore' => $quantityToRestore
            ]);
            return false;
        }

        // Add the quantity back to the most recent inventory record
        $inventory->update([
            'quantity_in_stock' => $inventory->quantity_in_stock + $quantityToRestore,
            'last_updated' => now()
        ]);

        // Create inventory update record
        InventoryUpdate::create([
            'inventory_id' => $inventory->id,
            'quantity_change' => $quantityToRestore, // Positive for restoration
            'reason' => $orderId ? "Stock restored - Order #{$orderId} cancelled" : 'Stock restoration',
            'updated_by' => $userId,
            'created_at' => now()
        ]);

        return [
            'inventory_id' => $inventory->id,
            'restored_quantity' => $quantityToRestore,
            'new_stock_level' => $inventory->quantity_in_stock + $quantityToRestore
        ];
    }

    /**
     * Check total available stock for either raw coffee or coffee product
     */
    public static function getAvailableStockByType($type, $itemId)
    {
        $column = $type === 'coffee_product' ? 'coffee_product_id' : 'raw_coffee_id';
        
        return self::where($column, $itemId)
            ->where('quantity_in_stock', '>', 0)
            ->sum('quantity_in_stock');
    }

    /**
     * Reduce inventory stock for either raw coffee or coffee product (FIFO - First In, First Out)
     */
    public static function reduceStockByType($type, $itemId, $quantityToReduce, $orderId = null, $userId = null)
    {
        $column = $type === 'coffee_product' ? 'coffee_product_id' : 'raw_coffee_id';
        
        $inventories = self::where($column, $itemId)
            ->where('quantity_in_stock', '>', 0)
            ->orderBy('created_at', 'asc') // FIFO - oldest stock first
            ->get();

        $remainingToReduce = $quantityToReduce;
        $reductions = [];

        foreach ($inventories as $inventory) {
            if ($remainingToReduce <= 0) {
                break;
            }

            $availableInThisInventory = $inventory->quantity_in_stock;
            $reductionFromThis = min($remainingToReduce, $availableInThisInventory);

            // Update the inventory record
            $inventory->update([
                'quantity_in_stock' => $inventory->quantity_in_stock - $reductionFromThis,
                'last_updated' => now()
            ]);

            // Create inventory update record
            InventoryUpdate::create([
                'inventory_id' => $inventory->id,
                'quantity_change' => -$reductionFromThis, // Negative for reduction
                'reason' => $orderId ? "Stock allocated - Order #{$orderId}" : 'Stock reduction',
                'updated_by' => $userId,
                'created_at' => now()
            ]);

            $reductions[] = [
                'inventory_id' => $inventory->id,
                'reduced_quantity' => $reductionFromThis,
                'remaining_stock' => $inventory->quantity_in_stock - $reductionFromThis
            ];

            $remainingToReduce -= $reductionFromThis;
        }

        return $reductions;
    }
}
