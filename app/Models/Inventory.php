<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'raw_coffee_id', 'coffee_product_id', 'quantity_in_stock', 'supply_center_id', 'last_updated', 'created_at', 'updated_at'
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
}
