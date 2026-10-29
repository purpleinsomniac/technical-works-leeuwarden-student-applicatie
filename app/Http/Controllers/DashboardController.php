<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
<<<<<<< HEAD
    public function index(Request $request): View
    {
        // Retrieve the currently authenticated user and eagerly load the app state.
        $user = $request->user();
        $user?->loadMissing('appState');
        $initialen = $user
            ? collect(preg_split('/\s+/', trim($user->naam)))
                ->filter()
                ->take(2)
                ->map(fn (string $naamdeel): string => mb_strtoupper(mb_substr($naamdeel, 0, 1)))
                ->implode('')
            : '';

        // Map the user's role to the corresponding Blade view.
        $roleView = match ($user?->rol) {
            'student' => 'roles.student',
            'coach' => 'roles.coach',
            'teamleider' => 'roles.teamleider',
            'beheerder' => 'roles.beheerder',
            default => null,
        };

        // Load the role-specific frontend assets for the dashboard shell.
        $roleAssets = match ($user?->rol) {
            'student' => [
                'style' => 'css/roles/student.css',
                'scripts' => ['js/roles/student.js'],
            ],
            'coach' => [
                'style' => 'css/roles/coach.css',
                'scripts' => ['js/common.js', 'js/roles/coach.js'],
            ],
            'teamleider' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/teamleider.js'],
            ],
            'beheerder' => [
                'style' => 'css/roles/beheerder.css',
                'scripts' => ['js/common.js', 'js/roles/beheerder.js'],
            ],
            default => [
                'style' => null,
                'scripts' => [],
            ],
        };

        // Pass the resolved role, user data, and app state to the shared app template.
        return view('app', [
            'user' => $user,
            'roleView' => $roleView,
            'roleAssets' => $roleAssets,
            'frontendUser' => $user ? [
                'id' => $user->id,
                'name' => $user->naam,
                'role' => $user->rol,
                'initials' => $initialen,
            ] : null,
            'frontendState' => $user?->appState?->gegevens ?? [],
            'frontendData' => $user?->rol === 'student' ? $this->studentData($user) : [],
            'roleData' => match ($user?->rol) {
                'coach' => $this->coachData(),
                'teamleider' => $this->teamleaderData(),
                'beheerder' => $this->administratorData(),
                default => [],
            },
        ]);
    }

=======
>>>>>>> fb49386d067a7b33bf790dd1a99e2e1ddd560682
    /**
     * Open het inlogscherm of stuur een gebruiker naar het eigen dashboard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! $request->user()) {
            return view('auth.login');
        }

        $route = match ($request->user()->rol) {
            'student' => 'student.dashboard',
            'coach' => 'coach.dashboard',
            'teamleider' => 'teamleader.dashboard',
            'beheerder' => 'admin.dashboard',
            default => 'login',
        };

        return redirect()->route($route);
    }
}
