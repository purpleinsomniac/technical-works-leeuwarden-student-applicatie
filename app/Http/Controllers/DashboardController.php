<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        // Retrieve the currently authenticated user and eagerly load the app state.
        $user = $request->user();
        $user?->loadMissing('appState');

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
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/coach.js'],
            ],
            'teamleider' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/teamleider.js'],
            ],
            'beheerder' => [
                'style' => null,
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
            ] : null,
            'frontendState' => $user?->appState?->gegevens ?? [],
        ]);
    }
}
