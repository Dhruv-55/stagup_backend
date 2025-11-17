<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venue;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;
class VenueController extends Controller
{
    public function venues(Request $request)
    {
        $venues = Venue::where('user_id', $request->user()->id)->get();
        return ResponseHelper::success($venues);
    }
    

    public function venueAdd(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'pin_code' => 'required|string',
            'capacity' => 'required|integer',
            // 'contact_email' => 'required|email',
            'contact_phone' => 'required|string',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::error($validation->errors()->first());
        }

        $venue = Venue::create([
            'user_id' => $request->user()->id,
            'name' => $request->name,
            "contact_email" => $request->email ?? $request->user()->email,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pin_code' => $request->pin_code,
            'capacity' => $request->capacity,
            // 'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
        ]);

        return ResponseHelper::success($venue);
    }


    public function venueEdit(Request $request,$id)
    {
        $venue = Venue::where('id', $id)->first();
        return ResponseHelper::success($venue);
    }

    public function venueUpdate(Request $request,$id)
    {
        $venue = Venue::where('id', $id)->first();

        $venue->update([
            'name' => $request->name,
            "contact_email" => $request->email ?? $request->user()->email,
            'description' => $request->description,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'capacity' => $request->capacity,
            'pin_code' => $request->pin_code,
            // 'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            "is_available" => $request->is_available,
        ]);
        return ResponseHelper::success($venue);
    }

    public function venueDelete(Request $request,$id)
    {
        $venue = Venue::where('id', $id)->first();
        $venue->delete();
        return ResponseHelper::success('Venue deleted successfully');
    }
}
