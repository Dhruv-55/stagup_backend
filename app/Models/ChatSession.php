<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $table = 'chat_sessions';
    protected $fillable = [
        'user_id',
        'start_at',
        'other_user_id',
        'end_at',
        'status',
        'total_messages',
    ];

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }
}
