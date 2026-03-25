<?php

use App\Http\Middleware\AuthGateway;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // AuthGateway debe correr en web para resolver sesión local/mock antes de auth.
        $middleware->web(prepend: [
            AuthGateway::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->expectsJson() || $request->is('sse/*')) {
                abort(401, 'Unauthenticated.');
            }

            return rtrim((string) config('services.auth_gateway.external_url', 'http://auth.example.com'), '/') . '/login';
        });

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
