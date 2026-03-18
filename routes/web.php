<?php

use App\Http\Controllers\LanguageController;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ArchivedLogController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth.gateway', 'auth'])->group(function () {
    // ArchivedLogs
    Route::get('/archived-logs', [ArchivedLogController::class, 'index'])->name('archived-logs.index');
    Route::get('/archived-logs/{id}', [ArchivedLogController::class, 'show'])->name('archived-logs.show');
    Route::delete('/archived-logs/{id}', [ArchivedLogController::class, 'destroy'])->name('archived-logs.destroy');

    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/logs', 'logs.index')->name('logs.index');
    Route::view('/error-codes', 'error-codes.index')->name('error-codes.index');

    Route::get('/test-comment-update', function () {
        $comment = Comment::findOrFail(1);
        Gate::authorize('update', $comment);

        return 'Puedes editar este comentario';
    });

    // Cerrar sesión
    Route::post('/logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away(
            rtrim((string) env('AUTH_EXTERNAL_URL', 'http://auth.example.com'), '/') . '/login'
        );
    })->name('logout');

    // Idiomas
    Route::post('/lang/{locale}', [LanguageController::class, 'switch'])
        ->name('lang.switch');
});