<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryUpdate extends Model
{
    use HasFactory;

    protected $table = 'inventory_updates';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // Disable automatic timestamps
    const CREATED_AT = 'created_at'; // Only created_at exists
    const UPDATED_AT = null; // No updated_at column
    protected $fillable = [
        'inventory_id', 'quantity_change', 'reason', 'updated_by', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'quantity_change' => 'decimal:2'
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
