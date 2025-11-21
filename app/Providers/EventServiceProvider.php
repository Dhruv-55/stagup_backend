<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
      
        \App\Events\OpenMicEventNotiSent::class => [
            \App\Listeners\OpenMicEventNotiSentListen::class,
        ],
    ];


    public function boot(): void
    {
        //
    }
    
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }
}
