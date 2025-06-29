<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [

        'id', 'sender_id', 'receiver_id', 'content', 'is_read'

    ];
    
    /**
     * Indicates if the model should be timestamped.
     * Set to true to use Laravel's automatic timestamps (created_at and updated_at)
     */
    public $timestamps = true;

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Get count of unread messages for a user
     * 
     * @param string $userId The user ID
     * @return int Number of unread messages
     */
    public static function getUnreadCount($userId)
    {
        return self::where('receiver_id', $userId)
                    ->where('is_read', 0)
                    ->count();
    }
}
