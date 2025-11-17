<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLocation;

class UserLocationController extends Controller
{
    public function isTodayLocationUpdated(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([
                'message' => $validator->errors()->first(),
            ], 422);
        }


        $userLocation = UserLocation::where('user_id', $request->user_id)->first();

        if(!$userLocation){
            return ResponseHelper::success([
                'isTodayLocationUpdated' => false,
            ], 200);
        }

        $isTodayLocationUpdated = $userLocation->updated_at->isToday();

        return ResponseHelper::success([
            'isTodayLocationUpdated' => $isTodayLocationUpdated,
        ]);
    }


    public function updateOrCreateLocation(Request $request){
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return ResponseHelper::error([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $userLocation = UserLocation::updateOrCreate([
            'user_id' => $request->user_id,
        ], [
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'pin_code' => $request->pin_code,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return ResponseHelper::success([
            'userLocation' => $userLocation,
        ]);
    }
}
