<?php

use App\Http\Controllers\ArchivedLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorCodeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Dashboard: contenido canónico en "/dashboard"
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // "/" redirige 301 a "/dashboard"
    Route::permanentRedirect('/', '/dashboard')->name('home');

    // ArchivedLogs
    Route::get('/archived-logs', [ArchivedLogController::class, 'index'])->name('archived-logs.index');
    Route::get('/archived-logs/{id}', [ArchivedLogController::class, 'show'])->whereNumber('id')->name('archived-logs.show');
    Route::delete('/archived-logs/{id}', [ArchivedLogController::class, 'destroy'])->whereNumber('id')->name('archived-logs.destroy');

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{id}', [LogController::class, 'show'])->whereNumber('id')->name('logs.show');
    Route::post('/logs/{id}/archive', [LogController::class, 'archive'])->whereNumber('id')->name('logs.archive');
    Route::patch('/logs/{id}/resolve', [LogController::class, 'resolve'])->whereNumber('id')->name('logs.resolve');

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
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(
            rtrim((string) config('services.auth_gateway.external_url', 'http://auth.example.com'), '/').'/login'
        );
    })->name('logout');

    // Idiomas
    Route::post('/lang/{locale}', [LanguageController::class, 'switch'])
        ->name('lang.switch');
});
