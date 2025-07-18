<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'warehouses';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'name', 'location', 'capacity', 'supplier_id', 'wholesaler_id', 'manager_name'
    ];

    /**
     * Get the supplier that owns this warehouse
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * Get the wholesaler that owns this warehouse
     */
    public function wholesaler()
    {
        return $this->belongsTo(Wholesaler::class, 'wholesaler_id');
    }

    /**
     * Get all inventory items in this warehouse
     */
    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'warehouse_id');
    }

    /**
     * Get all workers in this warehouse
     */
    public function workers()
    {
        return $this->hasMany(Worker::class, 'warehouse_id');
    }

    /**
     * Get the owner of this warehouse (supplier or wholesaler)
     */
    public function owner()
    {
        if ($this->supplier_id) {
            return $this->supplier();
        } elseif ($this->wholesaler_id) {
            return $this->wholesaler();
        }
        return null;
    }

    /**
     * Check if this warehouse belongs to a supplier
     */
    public function isSupplierWarehouse()
    {
        return !is_null($this->supplier_id);
    }

    /**
     * Check if this warehouse belongs to a wholesaler
     */
    public function isWholesalerWarehouse()
    {
        return !is_null($this->wholesaler_id);
    }
}
