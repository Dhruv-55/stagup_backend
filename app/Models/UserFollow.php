<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    protected $fillable = [
        'follower_id',
        'following_id',
    ];

    public static function followerCount($user_id){
        return UserFollow::where('following_id', $user_id)->count();
    }

    public static function followingCount($user_id){
        return UserFollow::where('follower_id', $user_id)->count();
    }

    public static function followerUserData($follower_id)
    {
        return User::with('profile')->find($follower_id);
    }

    public static function followingUserData($following_id)
    {
        return User::with('profile')->find($following_id);
    }

    public static function isFollow($following_id)
    {
        return UserFollow::where('following_id', $following_id)->exists();
    }
}
