<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserFollow;
use App\Models\EventLike;
use App\Models\Event;
use App\Helper\ResponseHelper;
class GenrealPurposeController extends Controller
{
    public function searchUser(Request $request)
    {
        \Log::info($request->all());
        $search = $request->input('search');
        $users = User::where('username', 'like', "%{$search}%")->with('profile')->where("id","!=",$request->user()->id)->get();
        return ResponseHelper::success($users);
    }

    public function followUser(Request $request)
    {
       $req_follow_id = $request->input('id');
       if($request->user()->id == $req_follow_id){
           return ResponseHelper::error('You cannot follow yourself');
       }
       $is_follow = UserFollow::where('follower_id', $request->user()->id)->where('following_id', $req_follow_id)->exists();
       if($is_follow){
           return ResponseHelper::error('You are already following this user');
       }
       $follow = UserFollow::create([
           'follower_id' => $request->user()->id,
           'following_id' => $req_follow_id
       ]);
        return ResponseHelper::success('Followed successfully');
    }
      public function unfollowUser(Request $request)
    {
       $req_follow_id = $request->input('id');
       if($request->user()->id == $req_follow_id){
           return ResponseHelper::error('You cannot unfollow yourself');
       }
       $is_follow = UserFollow::where('follower_id', $request->user()->id)->where('following_id', $req_follow_id)->exists();
       if(!$is_follow){
           return ResponseHelper::error('You are not following this user');
       }
       $follow = UserFollow::where('follower_id', $request->user()->id)->where('following_id', $req_follow_id)->delete();
        return ResponseHelper::success('Unfollowed successfully');
    }


    public function likeOrDislike(Request $request)
    {
        $req_event_id = $request->input('event_id');
        $is_like = EventLike::where('user_id', $request->user()->id)->where('event_id', $req_event_id)->exists();
        if($is_like){
            $like = EventLike::where('user_id', $request->user()->id)->where('event_id', $req_event_id)->delete();
            return ResponseHelper::success('Unliked successfully');
        }
        $like = EventLike::create([
            'user_id' => $request->user()->id,
            'event_id' => $req_event_id
        ]);
        return ResponseHelper::success('Liked successfully');
    }

    public function explore(Request $request){
        $perPage = $request->get('per_page', 30); 
        $page = $request->get('page', 1); 

        $location = $request->user_location;

        $pin_code = $location['userLocation']['pin_code'];
        $city = $location['userLocation']['city'];
        
        // Option 1: Use orderBy instead of inRandomOrder for consistent pagination
        $events = Event::with('user.profile', 'venue')
            ->leftJoin('venues', 'events.venue_id', '=', 'venues.id')
            ->select('events.*')
            ->orderByRaw("CASE WHEN venues.pin_code = ? THEN 1 WHEN venues.city = ? THEN 2 ELSE 3 END ASC", [$pin_code, $city])
            ->orderBy('events.created_at', 'desc') // secondary sort
            ->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'data' => $events->items(),
            
            'current_page' => $events->currentPage(),
            'per_page' => $events->perPage(),
            'total' => $events->total(),
            'last_page' => $events->lastPage(),
            'has_more' => $events->hasMorePages()
        ]);
    }

public function peopleMightKnow(Request $request)
{
    $location = $request->user_location;

    $pin_code = $location['userLocation']['pin_code'] ?? null;
    $city = $location['userLocation']['city'] ?? null;

    // Step 1: Get IDs the user follows
    $followedIds = UserFollow::where('follower_id', $request->user()->id)
        ->pluck('following_id');

    // Step 2: Get users not followed
    $users = User::with('profile')
        ->where('users.id', '!=', $request->user()->id)
        ->when($followedIds->isNotEmpty(), function ($q) use ($followedIds) {
            $q->whereNotIn('users.id', $followedIds);
        })
        ->leftJoin('user_locations', 'users.id', '=', 'user_locations.user_id')
        ->select('users.*') 
        ->orderByRaw("
            CASE
                WHEN user_locations.pin_code = ? THEN 1
                WHEN user_locations.city = ? THEN 2
                ELSE 3
            END
        ", [$pin_code, $city])
        ->orderBy('users.created_at', 'desc')
        ->limit(5)
        ->get();

    return ResponseHelper::success($users);
}


}
