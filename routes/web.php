<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController;
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
Route::post('/uitloggen', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::post('/demo/account-wisselen', [\App\Http\Controllers\DemoAccountController::class, 'switchAccount'])
    ->middleware(['auth','throttle:30,1'])->name('demo.account-switch');

Route::middleware('guest')->group(function (): void {
    // Een ingelogde gebruiker hoort nooit opnieuw een loginformulier met een
    // verouderd CSRF-token te zien; de guest-middleware stuurt die naar huis.
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/inloggen', [AuthController::class, 'login'])->name('login.submit');
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
    Route::patch('/mijn-profiel', [\App\Http\Controllers\ProfileController::class, 'update'])->middleware('throttle:10,1')->name('profile.update');
    Route::patch('/mijn-meldingsvoorkeuren', [\App\Http\Controllers\ProfileController::class, 'notifications'])->middleware('throttle:20,1')->name('profile.notifications');
    Route::put('/mijn-dashboardinstellingen', [DashboardSettingsController::class, 'update'])
        ->name('dashboard-settings.update');

    Route::get('/student', StudentDashboardController::class)->middleware('role:student')->name('student.dashboard');
    Route::middleware('role:coach')->prefix('coach')->name('coach.')->group(function (): void {
        Route::get('/', CoachDashboardController::class)->name('dashboard');
        Route::get('/groepen', [CoachDashboardController::class, 'groups'])->name('groups');
        Route::get('/groepen/{group}', [CoachDashboardController::class, 'group'])
            ->whereNumber('group')
            ->name('group');
        Route::get('/leeractiviteiten', [CoachDashboardController::class, 'activities'])->name('activities');
        Route::get('/leerdoelen', [CoachDashboardController::class, 'goals'])->name('goals');
        Route::get('/projecten', [CoachDashboardController::class, 'projects'])->name('projects');
        Route::get('/feedback', [CoachDashboardController::class, 'feedback'])->name('feedback');
        Route::get('/instellingen', [CoachDashboardController::class, 'settings'])->name('settings');
        Route::get('/studenten/{student}', [CoachDashboardController::class, 'student'])
            ->whereNumber('student')
            ->name('student');
    });
    Route::middleware('role:teamleider')->prefix('teamleider')->name('teamleader.')->group(function (): void {
        Route::get('/', TeamleaderDashboardController::class)->name('dashboard');
        Route::get('/groepen/{group}', [TeamleaderDashboardController::class, 'group'])
            ->whereNumber('group')
            ->name('group');
        Route::get('/groepen/{group}/studenten/{student}', [TeamleaderDashboardController::class, 'student'])
            ->whereNumber('group')
            ->whereNumber('student')
            ->name('student');
    });
    Route::get('/beheer', AdminDashboardController::class)->middleware('role:beheerder')->name('admin.dashboard');

    Route::middleware('role:beheerder')->prefix('beheer/leeruitkomsten')->name('admin.learning-outcomes.')->group(function (): void {
        Route::put('/{learningOutcome}', [LeeruitkomstController::class, 'update'])->name('update');
    });

    Route::put('/beheer/instellingen', [ApplicationSettingsController::class, 'update'])
        ->middleware('role:beheerder')
        ->name('admin.application-settings.update');

    Route::middleware('role:beheerder')->prefix('beheer/gebruikers')->name('admin.users.')->group(function (): void {
        Route::get('/sjabloon', [AdminUserController::class, 'downloadImportTemplate'])->name('template');
        Route::post('/importeren', [AdminUserController::class, 'import'])->name('import');
        Route::post('/importeren/bevestigen', [AdminUserController::class, 'confirmImport'])->name('import.confirm');
        Route::post('/importeren/annuleren', [AdminUserController::class, 'cancelImport'])->name('import.cancel');
        Route::post('/', [AdminUserController::class, 'store'])->name('store');
        Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:beheerder')->prefix('beheer/groepen')->name('admin.groups.')->group(function (): void {
        Route::post('/', [GroupController::class, 'store'])->name('store');
        Route::put('/{group}', [GroupController::class, 'update'])->name('update');
        Route::delete('/{group}', [GroupController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:student')->group(function (): void {
        Route::get('/student/punten/voortgang', [StudentDashboardController::class, 'pointProgress'])->name('student.points.progress');
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
    Route::post('/coach/feedback/{feedback}/verwerkt', \App\Http\Controllers\Coach\FeedbackReviewController::class)
        ->middleware('role:coach')->name('feedback.confirmProcessed');
});
