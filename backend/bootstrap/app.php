<?php

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response as GateResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'jwt'  => \Maya\Auth\Middleware\JwtMiddleware::class,
            'role' => \App\Http\Middleware\RequireRole::class,
        ]);
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
            \App\Http\Middleware\SetLocaleFromAcceptLanguage::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            $gateResponse = $e->response();
            if ($gateResponse instanceof GateResponse && is_string($gateResponse->code())) {
                return response()->json([
                    'error' => [
                        'code' => $gateResponse->code(),
                        'message' => $e->getMessage(),
                    ],
                ], $e->status() ?? 403);
            }

            return response()->json([
                'error' => [
                    'code' => 'forbidden',
                    'message' => __('api.auth.forbidden'),
                ],
            ], $e->status() ?? 403);
        });
    })->create();
