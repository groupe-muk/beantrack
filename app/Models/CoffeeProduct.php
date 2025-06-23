<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoffeeProduct extends Model
{
    use HasFactory;

    protected $table = 'coffee_product';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'raw_coffee_id', 'category', 'name', 'product_form', 'roast_level', 'production_date', 'created_at', 'updated_at'
    ];

    public function rawCoffee()
    {
        return $this->belongsTo(RawCoffee::class, 'raw_coffee_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'coffee_product_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'coffee_product_id');
    }

    /**
     * Calculate price for a given quantity
     */
    public function calculatePrice($quantity)
    {
        // Base price calculation logic (this should be implemented based on your business rules)
        // For now, we'll use a simple calculation
        $basePrice = 1000; // Base price per kg
        $markup = 0.20; // 20% markup
        
        return ($basePrice + ($basePrice * $markup)) * $quantity;
    }
}
