<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use App\Http\Middleware\AuthMock;

/**
 * Intenta enlazar la sesión del panel con un servicio de autenticación externo (cookie `session_token`
 * o Bearer). No sustituye al middleware `auth`: las rutas sensibles deben seguir protegidas con `auth`
 * y policies; este middleware solo puede llamar a `Auth::login()` si la validación remota tiene éxito.
 *
 * Flujo (no local): si no hay token o no hay URL base, no se llama al servicio y la petición continúa
 * sin usuario por este paso. En producción/staging, una URL mal configurada debe fallar al arrancar
 * (AuthExternalUrlGuard en AppServiceProvider). Tras intentar validar, si el token no es válido o no
 * hay usuario local asociado, la petición también continúa; el acceso queda otra vez en manos de `auth`.
 */
class AuthGateway
{
    public function __construct(private AuthMock $authMock){}

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            return $next($request);
        }

        // En local no se usa el servicio externo: sesión mock para desarrollo (ver AuthMock).
        if (app()->environment('local')) {
            return ($this->authMock)->handle($request, $next);
        }

        $token = $request->cookie('session_token') ?: $request->bearerToken();

        /*
        Sin token de sesión: se sigue la petición sin autenticar por este middleware.
        El acceso a rutas protegidas sigue dependiendo del middleware `auth` de Laravel (y de las policies).
        */
        if (!$token) {
            return $next($request);
        }

        $externalBaseUrl = rtrim((string) config('services.auth_gateway.external_url', ''), '/');

        /*
        Sin URL base externa (cadena vacía): no se puede llamar a /validate; mismo comportamiento de
        "paso" que arriba. En producción/staging, AppServiceProvider debería impedir arrancar con esta mala configuración.
        */
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

        /*
        Llegamos aquí si no hubo login (respuesta error, payload inválido, usuario inexistente, etc.).
        La petición sigue; la protección real sigue siendo `auth` en la ruta.
         */
        return $next($request);
    }
}
