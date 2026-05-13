<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Devuelve el perfil del usuario autenticado desde el JWT (incluye `permissions`
     * normalizados desde el claim del token; ver {@see \Maya\Auth\Middleware\JwtMiddleware}).
     */
    public function me(Request $request): JsonResponse
    {
        /** @var array<string, mixed>|null $jwtProfile */
        $jwtProfile = $request->attributes->get('jwt_user');

        return response()->json([
            'data' => $jwtProfile,
        ]);
    }
}
