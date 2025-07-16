<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wholesaler extends Model
{
    use HasFactory;

    protected $table = 'wholesaler';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'user_id', 'name', 'contact_person', 'email', 'phone', 'address', 'distribution_region', 'registration_number', 'approved_date', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'wholesaler_id');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'wholesaler_id');
    }

    public function segments()
    {
        return $this->belongsToMany(CustomerSegment::class, 'customer_segment_wholesaler', 'wholesaler_id', 'segment_id')
                    ->withPivot('scores')
                    ->withTimestamps();
    }
}
