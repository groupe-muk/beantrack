<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnalyticsData extends Model

{
        use HasFactory;

    protected $table = 'analytics_data';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id', 'type', 'data', 'generated_at', 'created_at', 'updated_at'
    ];
}
