<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Venue;
use App\Events\OpenMicEventNotiSent;


class EventController extends Controller
{
    public function events(Request $request)
    {
        $events = Event::where('user_id', $request->user()->id)->get();
        return ResponseHelper::success($events);
    }

    public function eventAdd(Request $request){
        $validation = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|string',
            'genre' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'entry_fee' => 'required|integer',
            // 'max_participants' => 'required|integer',
            'is_featured' => 'required|boolean',
        ]);
        if ($validation->fails()) {
            return ResponseHelper::error($validation->errors()->first());
        }
        $image =null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('events', 'public');
            $fullUrl = asset('storage/' . $path);
            $image = $fullUrl;
        }

        $event = Event::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            "venue_id" => $request->venue_id,
            'image' => $image,
            'type' => $request->type,
            'genre' => $request->genre,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'entry_fee' => $request->entry_fee,
            // 'max_participants' => $request->max_participants,
            'is_featured' => $request->is_featured,
        ]);
        if($event){

            $venue = Venue::select('pin_code', 'city')->find($event->venue_id);
            $near_location_users = User::with('userLocations')->whereHas('userLocations', function($query) use ($event, $venue) {
                $query->where('pin_code', $venue->pin_code)->orWhere('city', $venue->city);
            })->get();
         
            foreach($near_location_users as $user){
            event(new OpenMicEventNotiSent(
                $user,
                "New Update!",
                "Your event starts soon."
            ));
            }
            


        }
        return ResponseHelper::success($event);
    }


    public function eventEdit(Request $request,$id){
        $event = Event::where('id', $id)->first();
        return ResponseHelper::success($event);
    } 

    public function eventUpdate(Request $request,$id){
        $validation = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            // 'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'type' => 'required|string',
            'genre' => 'required|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
            'entry_fee' => 'required|integer',
            // 'max_participants' => 'required|integer',
            'is_featured' => 'required|boolean',
        ]);
        if ($validation->fails()) {
            return ResponseHelper::error($validation->errors()->first());
        }
        $event = Event::where('id', $id)->first();

        if($request->hasFile('image')){
            $file = $request->file('image');
            $path = $file->store('events', 'public');
            $fullUrl = asset('storage/' . $path);
            $event->image = $fullUrl;
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'type' => $request->type,
            'genre' => $request->genre,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'entry_fee' => $request->entry_fee,
            "venue_id" => $request->venue_id,
            // 'max_participants' => $request->max_participants,
            'is_featured' => $request->is_featured,
        ]);
        return ResponseHelper::success($event);
    }

    public function eventDelete(Request $request,$id){
        $event = Event::where('id', $id)->first();
        $event->delete();
        return ResponseHelper::success('Event deleted successfully');
    }
}
