<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReceiveUserLocation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         if ($request->hasHeader('X-User-Location')) {
            $location = json_decode($request->header('X-User-Location'), true);
            $request->merge(['user_location' => $location]);
        }
        return $next($request);
    }
}
