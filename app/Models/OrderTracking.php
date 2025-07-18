<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    use HasFactory;

    protected $table = 'order_trackings';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // Disable timestamps completely
    const CREATED_AT = null; // No created_at column
    const UPDATED_AT = 'updated_at'; // Only updated_at column exists
    
    protected $fillable = [
        'order_id', 'status', 'location', 'notes', 'updated_at'
    ];

    /**
     * Valid status values for order tracking
     */
    public const STATUSES = [
        'pending',
        'confirmed', 
        'shipped',
        'in-transit',
        'delivered',
        'cancelled',
        'rejected'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
