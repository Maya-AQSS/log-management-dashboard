<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AuthMock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('sse/*')) {
            return $next($request);
        }

        if (!Auth::check()) {
            Auth::loginUsingId((int) config('services.auth_gateway.mock_user_id', 1));
        }

        return $next($request);
    }
}
