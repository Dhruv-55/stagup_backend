<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\ResponseHelper;
use App\Models\User;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
// use Kreait\Firebase\Facades\Firebase;

use Kreait\Laravel\Firebase\Facades\Firebase;

class NotificationController extends Controller
{
     public function saveToken(Request $request)
    {
        
       $validation = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validation->fails()) {
            return ResponseHelper::error($validation->errors()->first());
        }

        $user = User::where("id", $request->user()->id)->first();

        if (!$user) {
            return ResponseHelper::error('User not found');
        }

        $user->update(['fcm_token' => $request->token]);
        $user->refresh();
        return ResponseHelper::success($user);
    }


     public function sendNotification()
    {
        $user = User::find(1); // change to real user
        $token = $user->fcm_token;

        if (!$token) {
            return response()->json(['error' => 'User has no FCM token']);
        }

        $messaging = Firebase::messaging();

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification(Notification::create('New Update!', 'Your event starts soon.'))
            ->withData([
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);

        $messaging->send($message);

        return response()->json(['success' => true, 'message' => 'Notification sent']);
    }
}
