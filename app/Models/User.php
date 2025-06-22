<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'id', 'name', 'email', 'email_verified_at', 'password', 'role', 'phone', 'remember_token', 'created_at', 'updated_at'
    ];
    
    protected $hidden = [
        'password', 'remember_token',
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
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
    
    public function isSupplier()
    {
        return $this->role === 'supplier';
    }
    
    public function isVendor()
    {
        return $this->role === 'vendor';
    }

    public function checkRole(string $role): bool
    {
        return $this->role === $role;
    }
    
    public function getDashboardRoute()
    {
        switch ($this->role) {
            case 'admin':
                return 'admin.dashboard';
            case 'supplier':
                return 'supplier.dashboard';
            case 'vendor':
                return 'vendor.dashboard';
            default:
                return 'dashboard';
        }
    }
}
