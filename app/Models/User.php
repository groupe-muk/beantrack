<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name', 'email', 'email_verified_at', 'password', 'remember_token', 'created_at', 'updated_at'
    ];

    public function supplier()
    {
        return $this->hasOne(Supplier::class, 'user_id');
    }
    public function wholesaler()
    {
        return $this->hasOne(Wholesaler::class, 'user_id');
    }
    public function inventoryUpdates()
    {
        return $this->hasMany(InventoryUpdate::class, 'updated_by');
    }
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }
    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }
    public function reports()
    {
        return $this->hasMany(Report::class, 'recipient_id');
    }
    public function vendorApplications()
    {
        return $this->hasMany(VendorApplication::class, 'applicant_id');
    }
}
