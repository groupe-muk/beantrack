<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wholesaler extends Model
{
    use HasFactory;

    protected $table = 'wholesaler';
    protected $fillable = [
        'user_id', 'name', 'contact_person', 'email', 'phone', 'address', 'distribution_region', 'registration_number', 'approved_date', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'wholesaler_id');
    }
}
