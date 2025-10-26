<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'venue_id',
        'title',
        'description',
        'image',
        'type',
        'genre',
        'start_time',
        'end_time',
        'entry_fee',
        'max_participants',
        'is_featured',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes(){
        return $this->hasMany(EventLike::class);
    }
}
