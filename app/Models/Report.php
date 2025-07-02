<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;
    
    protected $table = 'reports';
    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $fillable = [
        'name',
        'description',
        'type', 
        'recipient_id', 
        'frequency', 
        'format',
        'recipients',
        'schedule_time',
        'schedule_day',
        'status',
        'content', 
        'last_sent'
    ];

    protected $casts = [
        'content' => 'array',
        'last_sent' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                // Get the next ID from the database
                $lastReport = static::orderBy('id', 'desc')->first();
                if ($lastReport) {
                    $lastNumber = (int) substr($lastReport->id, 1);
                    $nextNumber = $lastNumber + 1;
                } else {
                    $nextNumber = 1;
                }
                $model->id = 'R' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByFrequency($query, $frequency)
    {
        return $query->where('frequency', $frequency);
    }

    // Accessors
    public function getFormattedLastSentAttribute()
    {
        return $this->last_sent ? $this->last_sent->format('Y-m-d H:i') : 'Never';
    }

    public function getStatusBadgeAttribute()
    {
        $statusClasses = [
            'active' => 'bg-green-100 text-green-800',
            'paused' => 'bg-gray-100 text-gray-800',
            'failed' => 'bg-red-100 text-red-800',
            'processing' => 'bg-yellow-100 text-yellow-800',
            'completed' => 'bg-blue-100 text-blue-800'
        ];

        $class = $statusClasses[$this->status] ?? 'bg-gray-100 text-gray-800';
        
        return "<span class=\"px-2 inline-flex text-xs leading-5 font-semibold rounded-full {$class}\">" . 
               ucfirst($this->status) . 
               "</span>";
    }

    public function getFormatBadgeAttribute()
    {
        $formatClasses = [
            'pdf' => 'bg-red-100 text-red-800',
            'excel' => 'bg-green-100 text-green-800',
            'csv' => 'bg-blue-100 text-blue-800',
            'dashboard' => 'bg-purple-100 text-purple-800'
        ];

        $icons = [
            'pdf' => 'fas fa-file-pdf',
            'excel' => 'fas fa-file-excel',
            'csv' => 'fas fa-file-csv',
            'dashboard' => 'fas fa-chart-bar'
        ];

        $class = $formatClasses[$this->format] ?? 'bg-gray-100 text-gray-800';
        $icon = $icons[$this->format] ?? 'fas fa-file';
        
        return "<span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$class}\">" .
               "<i class=\"{$icon} mr-1\"></i> " . strtoupper($this->format) .
               "</span>";
    }
}
