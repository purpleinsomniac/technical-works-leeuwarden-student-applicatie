<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Toon het formulier direct, zodat fout- en succesmeldingen bewaard blijven.
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->user()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    // Validate the submitted credentials and log the user in when they are correct.
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            // Show a validation message and keep the email typed in for the user.
            return back()
                ->withErrors(['email' => 'Deze inloggegevens zijn niet juist.'])
                ->onlyInput('email');
        }

        // Refresh the session to prevent fixation attacks after successful login.
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    // Log the user out and invalidate the current session.
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
