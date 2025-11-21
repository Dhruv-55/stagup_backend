<?php

namespace App\Services;

use Kreait\Laravel\Firebase\Facades\Firebase;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    /**
     * Send a notification to a single FCM token
     */
    public function sendToToken(string $token, string $title, string $body, array $data = [])
    {
        try {
            $messaging = Firebase::messaging();

            $message = CloudMessage::withTarget('token', $token)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            return $messaging->send($message);
            
        } catch (\Exception $e) {
            \Log::error("FCM sendToToken Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple tokens
     */
    public function sendToMany(array $tokens, string $title, string $body, array $data = [])
    {
        try {
            $messaging = Firebase::messaging();

            $message = CloudMessage::new()
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            return $messaging->sendMulticast($message, $tokens);
        
        } catch (\Exception $e) {
            \Log::error("FCM sendToMany Error: " . $e->getMessage());
            return false;
        }
    }
}
