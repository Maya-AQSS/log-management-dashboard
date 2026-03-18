<?php

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

$panelAuthMiddleware = app()->environment('local')
    ? ['auth.mock', 'auth']
    : ['auth.gateway', 'auth'];

Route::middleware($panelAuthMiddleware)->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/logs', 'logs.index')->name('logs.index');
    Route::view('/archived-logs', 'archived-logs.index')->name('archived-logs.index');
    Route::view('/error-codes', 'error-codes.index')->name('error-codes.index');

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
});
