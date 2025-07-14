<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawCoffee extends Model
{
    use HasFactory;

    protected $table = 'raw_coffee';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'supplier_id', 'coffee_type', 'grade', 'screen_size', 'defect_count', 'harvest_date', 'created_at', 'updated_at'
    ];

    protected $casts = [
        'defect_count' => 'integer',
        'harvest_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function coffeeProducts()
    {
        return $this->hasMany(CoffeeProduct::class, 'raw_coffee_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'raw_coffee_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'raw_coffee_id');
    }
}
