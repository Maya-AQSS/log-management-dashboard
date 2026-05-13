<?php

namespace App\Policies;

use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Http\Request;

/**
 * Alta / edición / baja de códigos de error: requiere permiso concedido en maya_authorization
 * y reflejado en el claim JWT `permissions` (véase {@see \Maya\Auth\Middleware\JwtMiddleware}).
 *
 * Código de permiso: {@see self::MANAGE_PERMISSION_CODE} (crear, actualizar y eliminar).
 */
class ErrorCodePolicy
{
    /** Permiso único de gestión de códigos de error en la app maya_logs. */
    public const MANAGE_PERMISSION_CODE = 'maya_logs.error_codes.manage';

    public function __construct(
        private readonly Request $request,
    ) {}

    public function create(?User $user): Response
    {
        return $this->managePermissionResponse();
    }

    public function update(?User $user, ErrorCode $errorCode): Response
    {
        return $this->managePermissionResponse();
    }

    public function delete(?User $user, ErrorCode $errorCode): Response
    {
        return $this->managePermissionResponse();
    }

    private function managePermissionResponse(): Response
    {
        if ($this->jwtHasPermission(self::MANAGE_PERMISSION_CODE)) {
            return Response::allow();
        }

        return Response::deny(__('api.error_codes.forbidden'), 'error_codes_permission_denied')->withStatus(403);
    }

    private function jwtHasPermission(string $code): bool
    {
        /** @var array<string, mixed>|null $jwtUser */
        $jwtUser = $this->request->attributes->get('jwt_user');
        $permissions = is_array($jwtUser) ? ($jwtUser['permissions'] ?? []) : [];

        if (! is_array($permissions)) {
            return false;
        }

        return in_array($code, $permissions, true);
    }
}
