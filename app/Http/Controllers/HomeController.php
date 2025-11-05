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
    $perPage = 10; // Number of posts per page
    $page = $request->input('page', 1);
    
    $events = Event::with(['venue', 'user.profile'])
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);
    
    $events->getCollection()->transform(function ($event) use ($request) {
        $event->total_likes = EventLike::where('event_id', $event->id)->count();
        $event->is_liked = EventLike::where('event_id', $event->id)
            ->where('user_id', $request->user()->id)
            ->exists();
        return $event;
    });
    
    return ResponseHelper::success([
        'data' => $events->items(),
        'current_page' => $events->currentPage(),
        'last_page' => $events->lastPage(),
        'has_more' => $events->hasMorePages()
    ]);
}
}
