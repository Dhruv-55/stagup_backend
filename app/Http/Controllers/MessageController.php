<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use App\Models\Chat;
use App\Models\ChatSession;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
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


    public function loadUser(Request $request){
        $user = User::with(["profile"])->where("id", $request->user_id)->first();
        
        // if(!$chat_session = ChatSession::where("user_id", $user->id)->orWhere("user_id", $request->user()->id)->first()){
        //     $chat_session = ChatSession::create([
        //         "user_id" => $user->id,
        //         // "session_id" => Str::uuid()->toString(),
        //         "start_at" => now()
        //     ]);
        // }


         $chat_session = ChatSession::where(function ($query) use ($request, $user) {
            $query->where('user_id', $request->user()->id)
                ->where('other_user_id', $user->id);
        })
        ->orWhere(function ($query) use ($request, $user) {
            $query->where('user_id', $user->id)
                ->where('other_user_id', $request->user()->id);
        })
        ->first();

        $chats = Chat::where("session_id", $chat_session->id)->orderBy("created_at", "desc")->get();

        return ResponseHelper::success([
            "user" => $user,
            "chats" => $chats,
            "chat_session" => $chat_session,
        ]);
    }


    public function loadChats(Request $request){
        // return $request->all();
        $otherUserId = $request->other_user_id;
        $chat_session = ChatSession::where(function ($query) use ($request, $otherUserId) {
            $query->where('user_id', $request->user()->id)
                ->where('other_user_id', $otherUserId);
        })
        ->orWhere(function ($query) use ($request, $otherUserId) {
            $query->where('user_id', $otherUserId)
                ->where('other_user_id', $request->user()->id);
        })
        ->first();

        if(!$chat_session){
            $chat_session = ChatSession::create([
                "user_id" => $request->user()->id,
                "other_user_id" => $otherUserId,
                "start_at" => now()
            ]);
        }
        $chats = Chat::where("session_id", $chat_session->id)->get();

        return ResponseHelper::success([
            "chats" => $chats,
            "chat_session" => $chat_session,
        ]);
    }


    public function sendMessage(Request $request){
        $validator = Validator::make($request->all(), [
            "session_id" => "required",
            "message" => "required",
            "user_id" => "required",
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error($validator->errors()->first());
        }

        $chat = Chat::create([
            "session_id" => $request->session_id,
            "user_id" => $request->user_id,
            "message" => $request->message,
            "sender_type" => "user",
        ]);

        return ResponseHelper::success($chat);
    }
}
