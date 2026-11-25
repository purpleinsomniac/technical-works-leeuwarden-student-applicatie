<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DemoAccountController extends Controller
{
    public function switchAccount(Request $request): RedirectResponse
    {
        // Laat alleen ingelogde gebruikers in een ingeschakelde lokale demo wisselen.
        abort_unless(app()->environment('local') && config('demo.account_switcher'), 404);
        abort_unless($request->user(), 403);

        // De gekozen sleutel verwijst naar een ingesteld account, nooit naar een vrij e-mailadres.
        $accounts = config('demo.accounts');
        $validated = $request->validate([
            'role' => ['required', Rule::in(array_keys($accounts))],
        ]);
        $accountKey = $validated['role'];
        $choice = $accounts[$accountKey];
        $account = User::query()
            ->where('email', $choice['email'])
            ->where('rol', $choice['role'] ?? $accountKey)
            ->firstOrFail();

        // Leeg de oude sessie volledig voordat het gekozen account wordt ingelogd.
        $session = $request->session();
        Auth::logout();
        $session->invalidate();
        $session->regenerateToken();
        Auth::login($account, false);
        $session->regenerate();

        return redirect()->route('home');
    }
}
