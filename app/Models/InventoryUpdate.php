<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryUpdate extends Model
{
    use HasFactory;

    protected $table = 'inventory_updates';
    protected $fillable = [
        'id', 'inventory_id', 'quantity_change', 'reason', 'updated_by', 'created_at'
    ];
    
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
