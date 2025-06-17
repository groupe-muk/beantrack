<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'supplier_id', 'wholesaler_id', 'raw_coffee_id', 'coffee_product_id', 'status', 'quantity', 'total_price', 'created_at', 'updated_at'
    ];

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
