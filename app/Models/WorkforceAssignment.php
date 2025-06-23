<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkforceAssignment extends Model
{
    use HasFactory;
    protected $table = 'workforce_assignments';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'worker_id', 'supply_center_id', 'role', 'start_date', 'end_date', 'created_at', 'updated_at'
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function supplyCenter()
    {
        return $this->belongsTo(SupplyCenter::class, 'supply_center_id');
    }
}
