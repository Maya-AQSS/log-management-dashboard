<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Devuelve el perfil del usuario autenticado desde el JWT.
     * En Fase 1 se proyecta el payload del token directamente; en futuras
     * fases se enriquecerá con permisos/equipos del servicio central.
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
