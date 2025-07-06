<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $fillable = [
        'supply_center_id', 'name', 'role', 'email',  'address',  'shift'
    ];

    public function workforceAssignments()
    {
        return $this->hasMany(WorkforceAssignment::class, 'worker_id');
    }

    // app/Models/Staff.php
public function supplycenter()
{
    return $this->belongsTo(SupplyCenter::class, 'supply_center_id');
}

}
