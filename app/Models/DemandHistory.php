<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandHistory extends Model
{
    use HasFactory;

    protected $table = 'demand_histories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'coffee_product_id',
        'demand_date',
        'demand_qty_tonnes',
    ];

    protected $casts = [
        'demand_date' => 'date',
        'demand_qty_tonnes' => 'decimal:4',
    ];

    /**
     * Relationship: belongs to a coffee product.
     */
    public function coffeeProduct()
    {
        return $this->belongsTo(CoffeeProduct::class, 'coffee_product_id');
    }
} 