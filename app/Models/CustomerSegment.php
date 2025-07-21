<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'segment_type',
        'is_dynamic',
    ];

    protected $casts = [
        'is_dynamic' => 'boolean',
    ];

    /**
     * Get the wholesalers that belong to this segment
     */
    public function wholesalers()
    {
        return $this->belongsToMany(Wholesaler::class, 'customer_segment_wholesaler', 'segment_id', 'wholesaler_id')
                    ->withPivot('scores')
                    ->withTimestamps();
    }

    /**
     * Scope for RFM segments
     */
    public function scopeRfm($query)
    {
        return $query->where('segment_type', 'rfm');
    }

    /**
     * Scope for order size segments
     */
    public function scopeOrderSize($query)
    {
        return $query->where('segment_type', 'order_size');
    }
}
