<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helper\ResponseHelper;

class MessageController extends Controller
{
    public function search(Request $request)
    {
        $search = $request->input('search');
        $users = User::where('username', 'like', "%{$search}%")->with(["profile","followers","following"])->where("id","!=",$request->user()->id)
        ->whereHas("followers", function ($query) use ($request) {
            $query->where("follower_id", $request->user()->id);
        })
        ->orWhereHas("following", function ($query) use ($request) {
            $query->where("following_id", $request->user()->id);
        })
        ->get();
        
        return ResponseHelper::success($users);
    }
}
