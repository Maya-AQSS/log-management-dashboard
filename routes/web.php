<?php

use App\Http\Controllers\ArchivedLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ErrorCodeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LogController;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth.gateway', 'auth'])->group(function () {
    // ArchivedLogs
    Route::get('/archived-logs', [ArchivedLogController::class, 'index'])->name('archived-logs.index');
    Route::get('/archived-logs/{id}', [ArchivedLogController::class, 'show'])->whereNumber('id')->name('archived-logs.show');
    Route::delete('/archived-logs/{id}', [ArchivedLogController::class, 'destroy'])->whereNumber('id')->name('archived-logs.destroy');

    // Logs
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{id}', [LogController::class, 'show'])->whereNumber('id')->name('logs.show');

    // SSE
    Route::get('/sse/logs', [LogController::class, 'stream'])->name('logs.stream');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::view('/error-codes', 'error-codes.index')->name('error-codes.index');

    // Temporary policy test    
    Route::get('/test-comment-update', function () {
        $comment = Comment::findOrFail(1);
        Gate::authorize('update', $comment);

        return 'Puedes editar este comentario';
    });

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
