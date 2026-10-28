<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\StudentDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/inloggen', [AuthController::class, 'login'])->middleware('guest')->name('login.submit');
Route::post('/uitloggen', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::put('/mijn-gegevens', [StateController::class, 'update'])->middleware('auth')->name('state.save');

Route::middleware('auth')->group(function (): void {
    Route::post('/mijn-reflecties', [StudentDataController::class, 'storeReflection'])->name('reflections.store');
    Route::delete('/mijn-reflecties/{reflection}', [StudentDataController::class, 'destroyReflection'])->name('reflections.destroy');
    Route::post('/mijn-feedback', [StudentDataController::class, 'storeFeedback'])->name('feedback.store');
    Route::delete('/mijn-feedback/{feedback}', [StudentDataController::class, 'destroyFeedback'])->name('feedback.destroy');
    Route::post('/mijn-leerdoelen', [StudentDataController::class, 'storeGoal'])->name('goals.store');
    Route::post('/mijn-opdrachten/{assignment}/inleveren', [StudentDataController::class, 'submitAssignment'])->name('assignments.submit');
    Route::patch('/coach/studenten/{student}/voortgang/{learningOutcome}', [ProgressController::class, 'update'])->name('progress.update');
});
