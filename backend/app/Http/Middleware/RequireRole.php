<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $jwtUser = $request->attributes->get('jwt_user');
        $userRoles = is_array($jwtUser) ? ($jwtUser['roles'] ?? []) : [];

        foreach ($roles as $required) {
            if (in_array($required, $userRoles, true)) {
                return $next($request);
            }
        }

        return response()->json([
            'error' => [
                'code' => 'required_role_missing',
                'message' => __('api.require_role.forbidden'),
            ],
        ], 403);
    }
}
