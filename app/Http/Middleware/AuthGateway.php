<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuthGateway
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        if (app()->environment('local')) {
            Auth::loginUsingId((int) env('AUTH_MOCK_USER_ID', 1));
            return $next($request);
        }

        $token = $request->cookie('session_token') ?: $request->bearerToken();

        if (!$token) {
            return $next($request);
        }

        $externalBaseUrl = rtrim((string) env('AUTH_EXTERNAL_URL', ''), '/');

        if ($externalBaseUrl === '') {
            return $next($request);
        }

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(3)
                ->get($externalBaseUrl . '/validate');

            if ($response->successful()) {
                $payload = $response->json();
                $externalId = $payload['id'] ?? null;

                if ($externalId !== null) {
                    $user = User::query()
                        ->where('external_id', (string) $externalId)
                        ->orWhere('id', (int) $externalId)
                        ->first();

                    if ($user) {
                        Auth::login($user);
                    }
                }
            }
        } catch (Throwable $e) {
            report($e);
        }

        return $next($request);
    }
}
