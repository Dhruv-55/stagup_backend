<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $fillable = [
        'session_id',
        'user_id',
        'sender_type',
        'message',
    ];


    public function session()
    {
        return $this->belongsTo(ChatSession::class);
    }
}
