<?php

use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use App\Models\Comment;

/* Rutas protegidas por el middleware AuthMock */
Route::middleware('auth.mock')->group(function () {
    // Home del panel
    Route::view('/', 'dashboard')->name('dashboard');
    Route::view('/logs', 'logs.index')->name('logs.index');
    Route::view('/archived-logs', 'archived-logs.index')->name('archived-logs.index');
    Route::view('/error-codes', 'error-codes.index')->name('error-codes.index');

    Route::post('/logout', function () {
        return redirect()->route('dashboard')->with('status', __('app.flash.logged_out'));
    })->name('logout');

    Route::post('/lang/{locale}', [LanguageController::class, 'switch'])
        ->name('lang.switch');

    // Ruta de prueba de policy (temporal)
    Route::get('/test-comment-update', function () {
        $comment = Comment::findOrFail(2);
        Gate::authorize('update', $comment);

        return 'Puedes editar este comentario';
    });
});
