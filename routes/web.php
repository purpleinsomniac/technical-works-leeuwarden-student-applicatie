<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StateController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/inloggen', [AuthController::class, 'login'])->middleware('guest')->name('login.submit');
Route::post('/uitloggen', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::put('/mijn-gegevens', [StateController::class, 'update'])->middleware('auth')->name('state.save');
