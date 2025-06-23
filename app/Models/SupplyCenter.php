<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplyCenter extends Model
{
    use HasFactory;

    protected $table = 'supply_centers';
    protected $fillable = [
        'id', 'name', 'location', 'capacity', 'created_at', 'updated_at'
    ];
    
    // Disable auto-incrementing as we're using string IDs
    public $incrementing = false;
    protected $keyType = 'string';

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
