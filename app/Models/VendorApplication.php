<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorApplication extends Model
{
    use HasFactory;
    protected $table = 'vendor_applications';
    protected $fillable = [
        'id', 'applicant_id', 'financial_data', 'references', 'license_data', 'status', 'visit_scheduled', 'created_at', 'updated_at'
    ];
    
    // Disable auto-incrementing as we're using string IDs
    public $incrementing = false;
    protected $keyType = 'string';

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
