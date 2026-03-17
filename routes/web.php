<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    return view('welcome');
});

/* Rutas protegidas por el middleware MockAuthUser */
Route::middleware('mock.auth')->group(function () {
    /* TODDO: Rutas directas a las vistas porque no hay controladores para probar el componente Layout */
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/logs', 'logs.index')->name('logs.index');
    Route::view('/archived-logs', 'archived-logs.index')->name('archived-logs.index');
    Route::view('/error-codes', 'error-codes.index')->name('error-codes.index');
});

Route::post('/logout', function () {
    return redirect()->route('dashboard')->with('status', 'Sesión cerrada');
})->name('logout');