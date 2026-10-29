<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
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
