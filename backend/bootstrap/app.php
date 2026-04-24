<?php

use App\Http\Middleware\AuthGateway;
use App\Http\Middleware\SetLocale;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Confiar en todos los proxies: el contenedor Docker corre detrás de nginx/Caddy.
        // Sin esto, $request->fullUrl() puede devolver el Host header forjado por el cliente
        // y permitir un open redirect via el parámetro return_to del SSO.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'jwt' => \Maya\Auth\Middleware\JwtMiddleware::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        // Orden crítico: sesión iniciada -> gateway SSO -> auth redirect.
        $middleware->priority([
            StartSession::class,
            AuthGateway::class,
            Authenticate::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->expectsJson() || $request->is('sse/*')) {
                abort(401, 'Unauthenticated.');
            }

            $authUrl = rtrim((string) config('services.auth_gateway.public_url', 'http://auth.example.com'), '/');

            return $authUrl.'?return_to='.urlencode($request->fullUrl());
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
