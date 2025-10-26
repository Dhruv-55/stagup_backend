<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventLike;
use App\Helper\ResponseHelper;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::with(['venue', 'user.profile'])->get();
        collect($events)->map(function ($event) use ($request) {
           $event->total_likes = EventLike::where('event_id', $event->id)->count();
           $event->is_liked = EventLike::where('event_id', $event->id)->where('user_id', $request->user()->id)->exists();
           return $event;
        });
        return ResponseHelper::success($events);
    }
}
