<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VendorApplication extends Model
{
    use HasFactory;
    protected $table = 'vendor_applications';
    protected $fillable = [
        'applicant_id', 'financial_data', 'references', 'license_data', 'status', 'visit_scheduled', 'created_at', 'updated_at'
    ];

    public function applicant()
    {
        return $this->belongsTo(User::class, 'applicant_id');
    }
}
