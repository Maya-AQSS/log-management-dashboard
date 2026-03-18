<?php

use App\Http\Middleware\AuthGateway;
use App\Http\Middleware\AuthMock;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.gateway' => AuthGateway::class,
            'auth.mock' => AuthMock::class,
        ]);

        $middleware->prependToPriorityList(
            \Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests::class,
            \App\Http\Middleware\AuthGateway::class
        );

        $middleware->redirectGuestsTo(function (Request $request): string {
            if ($request->expectsJson()) {
                abort(401, 'Unauthenticated.');
            }

            return rtrim((string) env('AUTH_EXTERNAL_URL', 'http://auth.example.com'), '/') . '/login';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
