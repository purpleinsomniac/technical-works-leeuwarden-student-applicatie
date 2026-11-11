<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /** Toon het formulier waarmee een gebruiker een herstellink aanvraagt. */
    public function request(): View
    {
        return view('auth.forgot-password');
    }

    /** Verstuur een herstellink zonder te onthullen of het adres bestaat. */
    public function email(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        Password::sendResetLink($credentials);

        return back()->with(
            'status',
            'Als dit e-mailadres bij ons bekend is, ontvangt u een e-mail met een veilige herstellink.'
        );
    }

    /** Toon het formulier dat bij een geldige herstellink hoort. */
    public function reset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->string('email')->toString(),
            'token' => $token,
        ]);
    }

    /** Controleer de token en sla het nieuwe wachtwoord veilig op. */
    public function update(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => 'Deze herstellink is ongeldig of verlopen. Vraag een nieuwe link aan.'])
                ->onlyInput('email');
        }

        return redirect()->route('login')->with('status', 'Uw wachtwoord is aangepast. U kunt nu inloggen.');
    }
}
