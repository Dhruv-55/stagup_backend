<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User,UserFollow,UserProfile,Event};
use App\Helper\ResponseHelper;
class ProfileController extends Controller
{
    public function index(Request $request)
    {
       $user = User::with('profile')->find($request->user()->id);
       return ResponseHelper::success($user);
    }

    public function update(Request $request)
    {
            $user = $request->user();

            // Create new or fetch existing profile
            $profile = UserProfile::firstOrNew(['user_id' => $user->id]);

            // Handle profile image upload if present
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $path = $file->store('avatars', 'public');
                // $profile->profile_image = $path;
                $fullUrl = asset('storage/' . $path);
                $profile->profile_image = $fullUrl;
            }

            // Fill or overwrite profile data
            $profile->display_name = $request->display_name;
            $profile->cover_image = $request->cover_image;
            $profile->bio = $request->bio;
            $profile->genre = $request->genre;
            $profile->city = $request->city;
            $profile->state = $request->state;
            $profile->instagram = $request->instagram;
            $profile->facebook = $request->facebook;
            
            // Save it (insert or update)
            $profile->save();

            // Load user with profile for response
            $user->load('profile');

            return ResponseHelper::success($user);
    }

    public function profileData(Request $request,$user_id=null)
    {
        $is_follow = false;
        if($user_id){
            $user = User::with('profile')->find($user_id);
            $is_follow = UserFollow::where('follower_id', $request->user()->id)->where('following_id', $user_id)->exists();
        }else{
            $user = User::with('profile')->find($request->user()->id);
        }


        // Add follower/following counts
        $user->followers = UserFollow::followerCount($user->id);
        $user->following = UserFollow::followingCount($user->id);
        $user->is_follow = $is_follow;
        return ResponseHelper::success($user);
    }


    public function posts(Request $request,$user_id=null)
    {
        if($user_id){
            $posts = Event::with('user.profile','venue')->where('user_id',$user_id)->get();
        }else{
            $posts = Event::with('user.profile','venue')->where('user_id',$request->user()->id)->get();
        }
        return ResponseHelper::success($posts);
    }

    public function followersList(Request $request,$user_id=null){

        if($user_id){
        $followers = UserFollow::where('following_id', $user_id)->get();
            $followers->each(function ($follower) {
                $follower->user = UserFollow::followerUserData($follower->follower_id);
                $follower->user->followers = UserFollow::followerCount($follower->follower_id);
                $follower->user->following = UserFollow::followingCount($follower->follower_id);
                $follower->user->is_follow = UserFollow::isFollow($follower->follower_id);
            });
        }else{
            $followers = UserFollow::where('following_id', $request->user()->id)->get();
            $followers->each(function ($follower) use ($request) {
                $follower->user = UserFollow::followerUserData($follower->follower_id);
                $follower->user->followers = UserFollow::followerCount($follower->follower_id);
                $follower->user->following = UserFollow::followingCount($follower->follower_id);
                $follower->user->is_follow = UserFollow::isFollow($request->user()->id);
            });
        }
        return ResponseHelper::success($followers);
    }

    public function followingList(Request $request,$user_id=null){
        if($user_id){
            $following = UserFollow::where('follower_id', $user_id)->get();
            $following->each(function ($following) {
                $following->user = UserFollow::followingUserData($following->following_id);
                $following->user->followers = UserFollow::followerCount($following->following_id);
                $following->user->following = UserFollow::followingCount($following->following_id);
                
            });
        }else{
            $following = UserFollow::where('follower_id', $request->user()->id)->get();
            $following->each(function ($following) {
                $following->user = UserFollow::followingUserData($following->following_id);
                $following->user->followers = UserFollow::followerCount($following->following_id);
                $following->user->following = UserFollow::followingCount($following->following_id);
            });
        }
        return ResponseHelper::success($following);
    }

   public function suggestionList(Request $request)
    {
        $user_id = $request->user()->id;

        // Get the IDs of users the logged-in user already follows
        $followingIds = UserFollow::where('follower_id', $user_id)
            ->pluck('following_id')
            ->toArray();

        // Suggest users that are NOT the current user and NOT already followed
        $suggestions = User::with('profile')
            ->whereNotIn('id', $followingIds)
            ->where('id', '!=', $user_id)
            ->get();

        $suggestions->each(function ($suggestion) {
            $suggestion->followers = UserFollow::followerCount($suggestion->id);
            $suggestion->following = UserFollow::followingCount($suggestion->id);
        });

        return ResponseHelper::success($suggestions);
    }

}
