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
        
        // Option 1: Use orderBy instead of inRandomOrder for consistent pagination
        $events = Event::with('user.profile', 'venue')
            ->orderBy('created_at', 'desc') // or 'id', 'desc'
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
}
