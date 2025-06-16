<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoffeeProduct extends Model
{
    use HasFactory;

    protected $table = 'coffee_product';
    protected $fillable = [
        'raw_coffee_id', 'category', 'name', 'product_form', 'roast_level', 'production_date', 'created_at', 'updated_at'
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
}
