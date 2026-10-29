<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return view('auth.login');
        }

        $route = match ($user->rol) {
            'student' => 'student.dashboard',
            'coach' => 'coach.dashboard',
            'teamleider' => 'teamleader.dashboard',
            'beheerder' => 'admin.dashboard',
            default => 'login',
        };

        return redirect()->route($route);
    }
}
