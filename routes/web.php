<?php

use App\Http\Controllers\ArchivedLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorCodeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{id}', [LogController::class, 'show'])->whereNumber('id')->name('logs.show');
    Route::get('/historico', [ArchivedLogController::class, 'index'])->name('archived-logs.index');
    Route::get('/historico/{id}', [ArchivedLogController::class, 'show'])->whereNumber('id')->name('archived-logs.show');
    Route::delete('/historico/{id}', [ArchivedLogController::class, 'destroy'])->whereNumber('id')->name('archived-logs.destroy');
    Route::get('/error-codes', [ErrorCodeController::class, 'index'])->name('error-codes.index');
    Route::get('/error-codes/{id}', [ErrorCodeController::class, 'show'])->whereNumber('id')->name('error-codes.show');
    Route::get('/sse/logs', [LogController::class, 'stream'])->name('logs.stream');

    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(
            rtrim((string) env('AUTH_EXTERNAL_URL', 'http://auth.example.com'), '/') . '/login'
        );
    })->name('logout');

    Route::post('/lang/{locale}', [LanguageController::class, 'switch'])
        ->name('lang.switch');
});
