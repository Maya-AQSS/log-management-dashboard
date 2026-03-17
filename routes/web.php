<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/* TODO
    Rutas directas a las vistas porque no hay controladores para probar el componente Layout 
*/
Route::view('/dashboard', 'dashboard')->name('dashboard');
Route::view('/logs', 'logs.index')->name('logs.index');
Route::view('/archived-logs', 'archived-logs.index')->name('archived-logs.index');
Route::view('/error-codes', 'error-codes.index')->name('error-codes.index');