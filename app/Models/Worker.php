<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $fillable = [
        'name', 'role', 'email', 'phone', 'address', 'created_at', 'updated_at'
    ];

    public function workforceAssignments()
    {
        return $this->hasMany(WorkforceAssignment::class, 'worker_id');
    }
}
