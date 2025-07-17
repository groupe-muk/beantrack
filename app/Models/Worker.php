<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id','name', 'role', 'email', 'phone', 'address', 'shift', 'supplycenter_id', 'warehouse_id', 'created_at', 'updated_at'
    ];

    public function workforceAssignments()
    {
        return $this->hasMany(WorkforceAssignment::class, 'worker_id');
    }

    public function supplyCenter()
    {
        return $this->belongsTo(SupplyCenter::class, 'supplycenter_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    /**
     * Check if worker belongs to a supply center
     */
    public function isSupplyCenterWorker()
    {
        return !is_null($this->supplycenter_id);
    }

    /**
     * Check if worker belongs to a warehouse
     */
    public function isWarehouseWorker()
    {
        return !is_null($this->warehouse_id);
    }

    /**
     * Get the location where this worker works (supply center or warehouse)
     */
    public function workLocation()
    {
        if ($this->supplycenter_id) {
            return $this->supplyCenter();
        } elseif ($this->warehouse_id) {
            return $this->warehouse();
        }
        return null;
    }
}
