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
        'id', 'user_id', 'supply_center_id', 'warehouse_id', 'name', 'contact_person', 'email', 'phone', 'address', 'registration_number', 'approved_date', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supplyCenter()
    {
        return $this->belongsTo(SupplyCenter::class, 'supply_center_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function warehouses()
    {
        return $this->hasMany(Warehouse::class, 'supplier_id');
    }

    public function rawCoffees()
    {
        return $this->hasMany(RawCoffee::class, 'supplier_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'supplier_id');
    }
}
