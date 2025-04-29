<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth; 
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;


Route::get('/', function () {
    return view('app');
});

// Rota para autenticação (login/logout)
Route::view('/login', 'auth.login')->name('login');
Route::post('/login', 'App\Http\Controllers\Auth\LoginController@login');
Route::post('/logout', 'App\Http\Controllers\Auth\LoginController@logout')->name('logout');

// Rota catch-all para a SPA
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');


// Rotas de autenticação simplificadas
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
