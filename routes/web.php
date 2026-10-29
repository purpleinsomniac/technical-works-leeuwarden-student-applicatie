<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Coach\DashboardController as CoachDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Coach\ProgressController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\DataController as StudentDataController;
use App\Http\Controllers\Student\StateController;
use App\Http\Controllers\Teamleader\DashboardController as TeamleaderDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/inloggen', [AuthController::class, 'login'])->middleware('guest')->name('login.submit');
Route::post('/uitloggen', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/student', StudentDashboardController::class)->middleware('role:student')->name('student.dashboard');
    Route::get('/coach', CoachDashboardController::class)->middleware('role:coach')->name('coach.dashboard');
    Route::get('/teamleider', TeamleaderDashboardController::class)->middleware('role:teamleider')->name('teamleader.dashboard');
    Route::get('/beheer', AdminDashboardController::class)->middleware('role:beheerder')->name('admin.dashboard');

    Route::middleware('role:student')->group(function (): void {
        Route::put('/mijn-gegevens', [StateController::class, 'update'])->name('state.save');
        Route::post('/mijn-reflecties', [StudentDataController::class, 'storeReflection'])->name('reflections.store');
        Route::delete('/mijn-reflecties/{reflection}', [StudentDataController::class, 'destroyReflection'])->name('reflections.destroy');
        Route::post('/mijn-feedback', [StudentDataController::class, 'storeFeedback'])->name('feedback.store');
        Route::delete('/mijn-feedback/{feedback}', [StudentDataController::class, 'destroyFeedback'])->name('feedback.destroy');
        Route::post('/mijn-leerdoelen', [StudentDataController::class, 'storeGoal'])->name('goals.store');
        Route::post('/mijn-opdrachten/{assignment}/inleveren', [StudentDataController::class, 'submitAssignment'])->name('assignments.submit');
    });

    Route::patch('/coach/studenten/{student}/voortgang/{learningOutcome}', [ProgressController::class, 'update'])
        ->middleware('role:coach')
        ->name('progress.update');
});
