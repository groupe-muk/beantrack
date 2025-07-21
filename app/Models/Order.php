<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'supplier_id', 'wholesaler_id', 'raw_coffee_id', 'coffee_product_id', 
        'status', 'quantity', 'total_price', 'order_date', 'total_amount', 'notes'
    ];

    protected $casts = [
        'order_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    /**
     * Get the order date attribute with null safety
     */
    public function getOrderDateAttribute($value)
    {
        if (empty($value) || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }
        
        try {
            return $this->asDate($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get formatted total price for display (whole numbers)
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 0);
    }

    /**
     * Get formatted total amount for display (whole numbers)  
     */
    public function getFormattedTotalAmountAttribute()
    {
        return number_format($this->total_amount, 0);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function wholesaler()
    {
        return $this->belongsTo(Wholesaler::class, 'wholesaler_id');
    }

    public function rawCoffee()
    {
        return $this->belongsTo(RawCoffee::class, 'raw_coffee_id');
    }

    public function coffeeProduct()
    {
        return $this->belongsTo(CoffeeProduct::class, 'coffee_product_id');
    }

    public function orderTrackings()
    {
        return $this->hasMany(OrderTracking::class, 'order_id');
    }
}
