<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Report extends Model
{
    use HasFactory;
    protected $table = 'reports';
    protected $fillable = [
        'id', 'type', 'recipient_id', 'frequency', 'content', 'last_sent', 'created_at', 'updated_at'
    ];
    
    // Disable auto-incrementing as we're using string IDs
    public $incrementing = false;
    protected $keyType = 'string';

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
