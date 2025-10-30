<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Models\Chat;
use App\Models\ChatSession;
class MessageController extends Controller
{
    public function search(Request $request)
    {
        if($request->search){
            $search = $request->input('search');
            $users = User::where('username', 'like', "%{$search}%")->with(["profile","followers","following"])->where("id","!=",$request->user()->id)
            ->whereHas("followers", function ($query) use ($request) {
                $query->where("follower_id", $request->user()->id);
            })
            ->orWhereHas("following", function ($query) use ($request) {
                $query->where("following_id", $request->user()->id);
            })
            ->get();

        }else{
           $users = User::with(["profile","followers","following","chatSessions"])
            ->withMax('chatSessions', 'created_at') // adds chat_sessions_max_created_at column
            ->where("id", "!=", $request->user()->id)
            ->where(function ($query) use ($request) {
                $query->whereHas("followers", function ($q) use ($request) {
                    $q->where("follower_id", $request->user()->id);
                })
                ->orWhereHas("following", function ($q) use ($request) {
                    $q->where("following_id", $request->user()->id);
                });
            })
            ->orderByDesc('chat_sessions_max_created_at')
            ->get();

        }

        collect($users)->map(function ($user) use($request){
            $last_chat_session = ChatSession::where("user_id", $user->id)->orWhere("user_id", $request->user()->id)->first();
            $last_message = null;
            if($last_chat_session){
                $last_message = Chat::where("session_id", $last_chat_session->id)->orderBy("created_at", "desc")->first();
            }
            $user->last_message = $last_message;
            return $user;
        });
        
        return ResponseHelper::success($users);
    }
}
