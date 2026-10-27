<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $user?->loadMissing('appState');

        $roleView = match ($user?->role) {
            'student' => 'roles.student',
            'coach' => 'roles.coach',
            'manager' => 'roles.teamleider',
            'admin' => 'roles.beheerder',
            default => null,
        };

        $roleAssets = match ($user?->role) {
            'student' => [
                'style' => 'css/roles/student.css',
                'scripts' => ['js/roles/student.js'],
            ],
            'coach' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/coach.js'],
            ],
            'manager' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/teamleider.js'],
            ],
            'admin' => [
                'style' => null,
                'scripts' => ['js/common.js', 'js/roles/beheerder.js'],
            ],
            default => [
                'style' => null,
                'scripts' => [],
            ],
        };

        return view('app', [
            'user' => $user,
            'roleView' => $roleView,
            'roleAssets' => $roleAssets,
            'frontendUser' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ] : null,
            'frontendState' => $user?->appState?->data ?? [],
        ]);
    }
}
