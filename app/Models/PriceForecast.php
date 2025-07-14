<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceForecast extends Model
{
    use HasFactory;

    protected $table = 'price_forecasts';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'coffee_product_id',
        'predicted_date',
        'predicted_price',
        'horizon',
        'generated_at',
    ];

    protected $casts = [
        'predicted_date' => 'date',
        'predicted_price' => 'decimal:4',
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