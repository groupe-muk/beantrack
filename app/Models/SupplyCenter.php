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
        'id', 'name', 'location', 'manager','capacity'
    ];
    
    // Corrected relationship: plural and correct class name
    public function workers() {
        return $this->hasMany(Worker::class, 'supply_center_id');
     }
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
