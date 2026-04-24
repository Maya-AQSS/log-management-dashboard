<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthMock
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('sse/*')) {
            return $next($request);
        }

        if (! Auth::check()) {
            Auth::loginUsingId((int) config('services.auth_gateway.mock_user_id', 1));
        }

        return $next($request);
    }
}
