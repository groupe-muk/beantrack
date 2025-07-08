<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    use HasFactory;

    protected $table = 'price_histories';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'coffee_product_id',
        'market_date',
        'price_per_lb',
    ];

    protected $casts = [
        'market_date' => 'date',
        'price_per_lb' => 'decimal:4',
    ];

    /**
     * Relationship: belongs to a coffee product.
     */
    public function coffeeProduct()
    {
        return $this->belongsTo(CoffeeProduct::class, 'coffee_product_id');
    }
} 