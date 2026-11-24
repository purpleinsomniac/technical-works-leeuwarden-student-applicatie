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
        abort_unless(app()->environment('local') && config('demo.account_switcher'), 404);
        abort_unless($request->user(), 403);
        $accounts = config('demo.accounts');
        $data = $request->validate(['role'=>['required', Rule::in(array_keys($accounts))]]);
        $choice = $accounts[$data['role']];
        $account = User::where('email', $choice['email'])->where('rol', $choice['role'] ?? $data['role'])->firstOrFail();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Auth::login($account, false);
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
