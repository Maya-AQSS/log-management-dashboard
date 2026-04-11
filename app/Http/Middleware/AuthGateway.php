<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

/**
 * Intenta enlazar la sesión del panel con un servicio de autenticación externo (cookie `session_token`
 * o Bearer). No sustituye al middleware `auth`: las rutas sensibles deben seguir protegidas con `auth`
 * y policies; este middleware solo puede llamar a `Auth::login()` si la validación remota tiene éxito.
 *
 * Flujo (no local): si no hay token o no hay URL base, no se llama al servicio y la petición continúa
 * sin usuario por este paso. En producción/staging, una URL mal configurada debe fallar al arrancar
 * (AuthExternalUrlGuard en AppServiceProvider). Tras intentar validar, si el token no es válido o no
 * hay usuario local asociado, la petición también continúa; el acceso queda otra vez en manos de `auth`.
 *
 * Logging: los pass-through se registran para auditoría. El caso `no_session_token` usa nivel `debug`
 * para no saturar logs en el flujo normal de invitados antes del redirect de `auth`.
 */
class AuthGateway
{
    public function __construct(private AuthMock $authMock) {}

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (Auth::check()) {
            return $next($request);
        }

        // Si el mock de sesión está habilitado explícitamente, se usa (desarrollo rápido).
        if (config('services.auth_gateway.mock_enabled', false)) {
            return $this->authMock->handle($request, $next);
        }

        $token = $request->cookie('session_token') ?: $request->bearerToken();

        /*
        Sin token de sesión: se sigue la petición sin autenticar por este middleware.
        El acceso a rutas protegidas sigue dependiendo del middleware `auth` de Laravel (y de las policies).
        */
        if (! $token) {
            $this->logPassThrough($request, 'no_session_token', 'debug');

            return $next($request);
        }

        $externalBaseUrl = rtrim((string) config('services.auth_gateway.external_url', ''), '/');

        /*
        Sin URL base externa (cadena vacía): no se puede llamar a /validate; mismo comportamiento de
        "paso" que arriba. En producción/staging, AppServiceProvider debería impedir arrancar con esta mala configuración.
        */
        if ($externalBaseUrl === '') {
            $this->logPassThrough($request, 'missing_external_auth_base_url');

            return $next($request);
        }

        $failureReason = 'unknown';
        $failureContext = [];

        try {
            $response = Http::acceptJson()
                ->withToken($token)
                ->timeout(3)
                ->get($externalBaseUrl.'/validate');

            if (! $response->successful()) {
                $failureReason = 'external_validate_http_not_successful';
                $failureContext['http_status'] = $response->status();
            } else {
                $payload = $response->json() ?? [];
                $externalId = $payload['id'] ?? null;

                if ($externalId === null) {
                    $failureReason = 'missing_external_id_in_payload';
                } else {
                    $user = User::query()
                        ->where('external_id', (string) $externalId)
                        ->orWhere('id', (int) $externalId)
                        ->first();

                    if ($user) {
                        Auth::login($user);
                    } else {
                        $failureReason = 'no_local_user_for_external_id';
                    }
                }
            }
        } catch (Throwable $e) {
            report($e);
            $failureReason = 'exception_during_validate';
            $failureContext['exception'] = $e::class;
        }

        /*
        Llegamos aquí si no hubo login (respuesta error, payload inválido, usuario inexistente, etc.).
        La petición sigue; la protección real sigue siendo `auth` en la ruta.
         */
        if (! Auth::check()) {
            $this->logPassThrough($request, 'after_external_validate_attempt', 'warning', array_merge([
                'failure_reason' => $failureReason,
            ], $failureContext));
        }

        return $next($request);
    }

    /**
     * @param  'debug'|'warning'  $level
     */
    private function logPassThrough(Request $request, string $reason, string $level = 'warning', array $extra = []): void
    {
        $context = array_merge([
            'reason' => $reason,
            'path' => $request->path(),
            'method' => $request->method(),
        ], $extra);

        $message = 'AuthGateway: pass-through without gateway authentication';

        if ($level === 'debug') {
            Log::debug($message, $context);
        } else {
            Log::warning($message, $context);
        }
    }
}
