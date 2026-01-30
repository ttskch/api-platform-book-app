<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateOptionally
{
    public function handle(Request $request, \Closure $next): Response
    {
        if (Auth::guard('api')->check()) {
            Auth::shouldUse('api');
        }

        return $next($request);
    }
}
