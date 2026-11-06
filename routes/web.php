<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ApplicationSettingsController;
use App\Http\Controllers\Admin\LeeruitkomstController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Coach\DashboardController as CoachDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardSettingsController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\Coach\ProgressController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\DataController as StudentDataController;
use App\Http\Controllers\Student\StateController;
use App\Http\Controllers\Teamleader\DashboardController as TeamleaderDashboardController;
use App\Http\Middleware\EnforceMaintenanceMode;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(EnforceMaintenanceMode::class)
    ->name('home');
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/inloggen', [AuthController::class, 'login'])->name('login.submit');
Route::post('/uitloggen', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('guest')->group(function (): void {
    Route::get('/wachtwoord-vergeten', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/wachtwoord-vergeten', [PasswordResetController::class, 'email'])
        ->middleware('throttle:5,1')
        ->name('password.email');
    Route::get('/wachtwoord-herstellen/{token}', [PasswordResetController::class, 'reset'])->name('password.reset');
    Route::post('/wachtwoord-herstellen', [PasswordResetController::class, 'update'])
        ->middleware('throttle:5,1')
        ->name('password.update');
});

Route::middleware(['auth', EnforceMaintenanceMode::class])->group(function (): void {
    Route::put('/mijn-dashboardinstellingen', [DashboardSettingsController::class, 'update'])
        ->name('dashboard-settings.update');

    Route::get('/student', StudentDashboardController::class)->middleware('role:student')->name('student.dashboard');
    Route::get('/coach', CoachDashboardController::class)->middleware('role:coach')->name('coach.dashboard');
    Route::get('/teamleider', TeamleaderDashboardController::class)->middleware('role:teamleider')->name('teamleader.dashboard');
    Route::get('/beheer', AdminDashboardController::class)->middleware('role:beheerder')->name('admin.dashboard');

    Route::middleware('role:beheerder')->prefix('beheer/leeruitkomsten')->name('admin.learning-outcomes.')->group(function (): void {
        Route::put('/{learningOutcome}', [LeeruitkomstController::class, 'update'])->name('update');
    });

    Route::put('/beheer/instellingen', [ApplicationSettingsController::class, 'update'])
        ->middleware('role:beheerder')
        ->name('admin.application-settings.update');

    Route::middleware('role:beheerder')->prefix('beheer/gebruikers')->name('admin.users.')->group(function (): void {
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:student')->group(function (): void {
        Route::post('/mijn-challenges/synchroniseren', function (\Illuminate\Http\Request $request, \App\Services\ChallengeService $challenges) {
            return response()->json($challenges->sync($request->user()));
        })->name('challenges.sync');
        Route::put('/mijn-gegevens', [StateController::class, 'update'])->name('state.save');
        Route::post('/mijn-reflecties', [StudentDataController::class, 'storeReflection'])->name('reflections.store');
        Route::put('/mijn-reflecties/{reflection}', [StudentDataController::class, 'storeReflection'])->name('reflections.update');
        Route::delete('/mijn-reflecties/{reflection}', [StudentDataController::class, 'destroyReflection'])->name('reflections.destroy');
        Route::post('/mijn-reflecties/{id}/herstellen', function (\Illuminate\Http\Request $request, string $id) {
            $reflection = \App\Models\Reflection::withTrashed()->where('gebruiker_id',$request->user()->id)->findOrFail($id);
            $reflection->restore();
            return response()->json(['restored'=>true]);
        })->name('reflections.restore');
        Route::post('/mijn-feedback', [StudentDataController::class, 'storeFeedback'])->name('feedback.store');
        Route::delete('/mijn-feedback/{feedback}', [StudentDataController::class, 'destroyFeedback'])->name('feedback.destroy');
        Route::post('/mijn-leerdoelen', [StudentDataController::class, 'storeGoal'])->name('goals.store');
        Route::post('/mijn-leerdoelen/{goal}/afronden', function (\Illuminate\Http\Request $request, \App\Models\LearningGoal $goal) {
            abort_unless($goal->gebruiker_id === $request->user()->id, 403);
            $goal->update(['status'=>'afgerond']);
            return response()->json(['id'=>$goal->id,'status'=>$goal->status]);
        })->name('goals.complete');
        Route::post('/mijn-opdrachten/{assignment}/inleveren', [StudentDataController::class, 'submitAssignment'])->name('assignments.submit');
        Route::post('/mijn-opdrachten/{assignment}/terugzetten', [StudentDataController::class, 'reopenAssignment'])->name('assignments.reopen');
        Route::post('/mijn-opdrachten/{assignment}/bestanden', [StudentDataController::class, 'storeAssignmentFile'])->name('assignments.files.store');
        Route::get('/mijn-opdrachtbestanden/{assignmentFile}', [StudentDataController::class, 'downloadAssignmentFile'])->name('assignments.files.download');
        Route::delete('/mijn-opdrachtbestanden/{assignmentFile}', [StudentDataController::class, 'destroyAssignmentFile'])->name('assignments.files.destroy');
    });

    Route::patch('/coach/studenten/{student}/voortgang/{learningOutcome}', [ProgressController::class, 'update'])
        ->middleware('role:coach')
        ->name('progress.update');
    Route::post('/coach/feedback/{feedback}/verwerkt', function (\Illuminate\Http\Request $request, \App\Models\Feedback $feedback, \App\Services\ChallengeService $challenges) {
        abort_unless($feedback->auteur_id === $request->user()->id && $feedback->type === 'received', 403);
        if (!$feedback->processed_confirmed_at) {
            $feedback->forceFill(['processed_confirmed_at'=>now()])->save();
        }
        $challenges->sync($feedback->student);
        return back()->with('status', 'Verwerkte feedback bevestigd.');
    })->middleware('role:coach')->name('feedback.confirmProcessed');
});
