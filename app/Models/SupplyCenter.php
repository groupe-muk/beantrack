<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyCenter extends Model
{
    use HasFactory;

    protected $table = 'supply_centers';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'name', 'location', 'capacity', 'created_at', 'updated_at'
    ];

    public function suppliers()
    {
        return $this->hasMany(Supplier::class, 'supply_center_id');
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class, 'supply_center_id');
    }

    public function workforceAssignments()
    {
        return $this->hasMany(WorkforceAssignment::class, 'supply_center_id');
    }
}
