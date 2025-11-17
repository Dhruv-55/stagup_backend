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
        $location = $request->user_location;

        $pin_code = $location['userLocation']['pin_code'];
        $city = $location['userLocation']['city'];

        // $events = Event::with(['venue', 'user.profile'])
        //     ->orderBy('created_at', 'desc')
        //     ->whereHas('venue', function ($query) use ($pin_code, $city) {
        //         $query->where('pin_code', $pin_code)
        //             ->orWhere('city', $city);
        //     })
        //     ->paginate($perPage, ['*'], 'page', $page);
        

        $events = Event::with(['venue', 'user.profile'])
            ->leftJoin('venues', 'events.venue_id', '=', 'venues.id')
            ->select('events.*')
            ->orderByRaw("
                CASE
                    WHEN venues.pin_code = ? THEN 1
                    WHEN venues.city = ? THEN 2
                    ELSE 3
                END ASC
            ", [$pin_code, $city])
            ->orderBy('events.created_at', 'desc') // secondary sort
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
