<?php

namespace App\Listeners;

use App\Events\OpenMicEventNotiSent;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;


class OpenMicEventNotiSentListen
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OpenMicEventNotiSent $event): void
    {
         $notificationService = app(FirebaseNotificationService::class);
         $notificationService->sendToToken(
            $event->user->fcm_token,
            $event->title,
            $event->body
        );
    }
}
