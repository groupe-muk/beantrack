<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'user_id', 'name', 'address', 'contact_person', 'phone', 'region', 'country', 'description', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplyCenter()
    {
        return $this->belongsTo(SupplyCenter::class, 'supply_center_id');
    }

    public function rawCoffees()
    {
        return $this->hasMany(RawCoffee::class, 'supplier_id');
    }
}
