<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $fillable = [
        'id', 'name', 'role', 'email', 'phone', 'address', 'created_at', 'updated_at'
    ];
    
    // Disable auto-incrementing as we're using string IDs
    public $incrementing = false;
    protected $keyType = 'string';

    public function workforceAssignments()
    {
        return $this->hasMany(WorkforceAssignment::class, 'worker_id');
    }
}
