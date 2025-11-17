<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
       $middleware->use([
            \App\Http\Middleware\Cors::class,
            \App\Http\Middleware\ReceiveUserLocation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
         $exceptions->respond(function ($response, $exception, $request) {
            if ($request->is('api/*')) {
                $response->headers->set('Access-Control-Allow-Origin', '*');
                $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
                $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With', 'X-User-Location');
            }
            return $response;
        });
    })->create();
