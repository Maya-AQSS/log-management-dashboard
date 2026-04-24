<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

/**
 * Intenta enlazar la sesión del panel con un servicio de autenticación externo (query token `session_token`
 * o Bearer). No sustituye al middleware `auth`: las rutas sensibles deben seguir protegidas con `auth`
 * y policies; este middleware solo puede llamar a `Auth::login()` si la validación remota tiene éxito.
 *
 * Flujo (no local): si no hay token o no hay URL base, no se llama al servicio y la petición continúa
 * sin usuario por este paso. En producción/staging, una URL mal configurada debe fallar al arrancar
 * (AuthExternalUrlGuard en AppServiceProvider). Tras intentar validar, si el token no es válido o no
 * hay usuario local asociado, la petición también continúa; el acceso queda otra vez en manos de `auth`.
 *
 * Cache: las autenticaciones exitosas se cachean 60 s (clave SHA-256 del token) para evitar una llamada
 * HTTP + query DB en cada request. Solo se cachean hits válidos; tokens inválidos no se cachean.
 *
 * Logging: los pass-through se registran para auditoría. El caso `no_session_token` usa nivel `debug`
 * para no saturar logs en el flujo normal de invitados antes del redirect de `auth`.
 */
class AuthGateway
{
    private const CACHE_TTL_SECONDS = 60;

    private const HTTP_TIMEOUT_SECONDS = 3;

    private const VALIDATE_ENDPOINT = '/api/v1/auth/token/validate';

    public function __construct(private AuthMock $authMock) {}

    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        if (Auth::check()) {
            return $next($request);
        }

        // El mock de sesión solo debe estar disponible en local/testing.
        if (config('services.auth_gateway.mock_enabled', false)) {
            if (! app()->environment(['local', 'testing'])) {
                throw new \RuntimeException('AUTH_MOCK_ENABLED solo puede usarse en local/testing.');
            }

            return $this->authMock->handle($request, $next);
        }

        $queryToken = $request->query('session_token');
        $hasQueryToken = is_string($queryToken) && $queryToken !== '';
        $token = $hasQueryToken
            ? $queryToken
            : $request->bearerToken();

        /*
        Sin token de sesión: se sigue la petición sin autenticar por este middleware.
        El acceso a rutas protegidas sigue dependiendo del middleware `auth` de Laravel (y de las policies).
        */
        if (! $token) {
            $this->logPassThrough($request, 'no_session_token', 'debug');

            return $next($request);
        }

        // Cache hit: autenticación ya validada recientemente, skip de la llamada HTTP.
        $cacheKey = 'auth_gateway:'.hash('sha256', $token);
        if ($cachedUserId = Cache::get($cacheKey)) {
            $user = User::find($cachedUserId);
            if ($user) {
                Auth::login($user);

                return $next($request);
            }
            // El usuario fue eliminado: limpiar la entrada obsoleta de cache.
            Cache::forget($cacheKey);
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
                ->withHeaders([
                    'X-App-Key' => config('services.auth_gateway.api_key', ''),
                ])
                ->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->post($externalBaseUrl.self::VALIDATE_ENDPOINT, [
                    'app' => config('services.auth_gateway.app_slug', 'maya-logs'),
                ]);

            if (! $response->successful()) {
                $failureReason = 'external_validate_http_not_successful';
                $failureContext['http_status'] = $response->status();
            } else {
                $payload = $response->json() ?? [];
                $user = $this->resolveUser($payload);

                if ($user === null && ($payload['user']['id'] ?? null) === null) {
                    $failureReason = 'missing_external_id_in_payload';
                } elseif ($user === null) {
                    $failureReason = 'no_local_user_for_external_id';
                } else {
                    Auth::login($user);
                    Cache::put($cacheKey, $user->id, self::CACHE_TTL_SECONDS);
                }
            }
        } catch (Throwable $e) {
            report($e);
            $failureReason = 'exception_during_validate';
            $failureContext['exception'] = $e::class;
            $failureContext['exception_message'] = $e->getMessage();
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

        if ($hasQueryToken) {
            return redirect($this->buildLocalUrlWithoutSessionToken($request));
        }

        return $next($request);
    }

    /**
     * Busca el usuario local por external_id del payload. Devuelve null si falta el campo o no hay match.
     *
     * @param  array<string, mixed>  $payload
     */
    private function resolveUser(array $payload): ?User
    {
        $userData = $payload['user'] ?? null;

        if ($userData === null || ! isset($userData['id'])) {
            return null;
        }

        if (! is_scalar($userData['id'])) {
            return null;
        }

        return User::updateOrCreate(
            ['external_id' => (string) $userData['id']],
            [
                'name' => $userData['name'] ?? 'Unknown',
                'email' => $userData['email'] ?? 'unknown@example.com',
            ]
        );
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

        Log::{$level}($message, $context);
    }

    private function buildLocalUrlWithoutSessionToken(Request $request): string
    {
        $query = $request->query();
        unset($query['session_token']);

        $path = '/'.ltrim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        if (empty($query)) {
            return $path;
        }

        return $path.'?'.http_build_query($query);
    }
}
