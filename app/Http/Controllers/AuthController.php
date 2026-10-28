<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Redirect the user to the main dashboard entry point.
    public function showLogin(): RedirectResponse
    {
        return redirect()->route('home');
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
