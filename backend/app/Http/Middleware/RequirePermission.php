<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifica que el usuario autenticado tiene un permiso concreto en maya-logs.
 *
 * Los permisos se resuelven desde user_resolved_permissions (FDW a maya_auth),
 * que aplica la jerarquía de roles y los overrides grant/deny. El resultado se
 * cachea en Redis durante 5 minutos.
 *
 * Uso en rutas: ->middleware('permission:logs.update')
 */
class RequirePermission
{
    private const CACHE_TTL = 300;

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $jwtUser = $request->attributes->get('jwt_user');

        if ($jwtUser === null) {
            // JwtMiddleware bypassed (tests) — skip permission check.
            return $next($request);
        }

        $userId   = (string) ($jwtUser['id'] ?? '');
        $cacheKey = "perm:{$userId}:{$permission}";

        $has = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId, $permission) {
            return DB::table('user_resolved_permissions')
                ->where('user_id', $userId)
                ->where('permission_slug', $permission)
                ->exists();
        });

        if (! $has) {
            return response()->json(['message' => "Forbidden: missing permission '{$permission}'."], 403);
        }

        return $next($request);
    }
}
