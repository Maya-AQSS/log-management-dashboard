<?php

use App\Http\Controllers\ArchivedLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorCodeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\LogoutController;
use App\Http\Middleware\AuthGateway;
use Illuminate\Support\Facades\Route;

Route::middleware([AuthGateway::class, 'auth'])->group(function () {
    // Dashboard: contenido canónico en "/dashboard"
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // "/" redirige 301 a "/dashboard"
    Route::permanentRedirect('/', '/dashboard')->name('home');

    // ArchivedLogs
    Route::prefix('archived-logs')->group(function () {
        Route::get('/', [ArchivedLogController::class, 'index'])->name('archived-logs.index');
        Route::get('/{id}', [ArchivedLogController::class, 'show'])->whereNumber('id')->name('archived-logs.show');
        Route::delete('/{id}', [ArchivedLogController::class, 'destroy'])->whereNumber('id')->name('archived-logs.destroy');
    });

    // Logs
    Route::prefix('logs')->group(function () {
        Route::get('/', [LogController::class, 'index'])->name('logs.index');
        Route::get('/{id}', [LogController::class, 'show'])->whereNumber('id')->name('logs.show');
        Route::post('/{id}/archive', [LogController::class, 'archive'])->whereNumber('id')->name('logs.archive');
        Route::patch('/{id}/resolve', [LogController::class, 'resolve'])->whereNumber('id')->name('logs.resolve');
    });

    // SSE
    Route::get('/sse/logs', [LogController::class, 'stream'])->name('logs.stream');

    // Error codes
    Route::get('/error-codes', [ErrorCodeController::class, 'index'])->name('error-codes.index');
    Route::get('/error-codes/create', [ErrorCodeController::class, 'create'])->name('error-codes.create');
    Route::post('/error-codes', [ErrorCodeController::class, 'store'])->name('error-codes.store');
    Route::put('/error-codes/{id}', [ErrorCodeController::class, 'update'])->whereNumber('id')->name('error-codes.update');
    Route::get('/error-codes/{id}', [ErrorCodeController::class, 'show'])->whereNumber('id')->name('error-codes.show');
    Route::delete('/error-codes/{id}', [ErrorCodeController::class, 'destroy'])->whereNumber('id')->name('error-codes.destroy');

    // Logout
    Route::post('/logout', LogoutController::class)->name('logout');

    // Idiomas
    Route::post('/lang/{locale}', [LanguageController::class, 'switch'])
        ->name('lang.switch');
});
