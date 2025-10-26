<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserProfile extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'display_name',
        'profile_image',
        'cover_image',
        'bio',
        'genre',
        'city',
        'state',
        'country',
        'instagram',
        'facebook',
        'youtube',
        'website',
        'is_verified',
    ];

    public function followers()
    {
        return $this->hasMany(UserFollow::class, 'follower_id', 'user_id');
    }
}
