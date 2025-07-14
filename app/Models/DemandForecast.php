<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DemandForecast extends Model
{
    use HasFactory;

    protected $table = 'demand_forecasts';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'coffee_product_id',
        'predicted_date',
        'predicted_demand_tonnes',
        'horizon',
        'generated_at',
    ];

    protected $casts = [
        'predicted_date' => 'date',
        'predicted_demand_tonnes' => 'decimal:4',
        'generated_at' => 'datetime',
    ];

    /**
     * Relationship: belongs to a coffee product.
     */
    public function coffeeProduct()
    {
        return $this->belongsTo(CoffeeProduct::class, 'coffee_product_id');
    }
} 