<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function notifications(Request $request): JsonResponse
    {
        $data = $request->validate(self::notificationRules());
        $state = $request->user()->appState()->firstOrNew();
        $profile = array_replace($state->gegevens['profile-settings'] ?? [], array_intersect_key($data, array_flip(['email_notifications','platform_notifications'])));
        $state->gegevens = array_replace($state->gegevens ?? [], ['notification-settings'=>$data, 'profile-settings'=>$profile]);
        $state->save();
        return response()->json(['saved'=>true]);
    }

    public static function notificationRules(): array
    {
        return [
            'feedback'=>['required','boolean'], 'grades'=>['required','boolean'],
            'deadlines'=>['required','boolean'], 'projects'=>['required','boolean'],
            'goals'=>['required','boolean'], 'platform_notifications'=>['required','boolean'],
            'email_notifications'=>['required','boolean'], 'weekly_summary'=>['required','boolean'],
            'do_not_disturb'=>['required','boolean'], 'deadline_days'=>['required','integer','in:0,1,2,7'],
        ];
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email_notifications' => ['required', 'boolean'],
            'platform_notifications' => ['required', 'boolean'],
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);
        $user = $request->user();
        $state = $user->appState()->firstOrNew();
        $settings = $state->gegevens['profile-settings'] ?? [];
        if (!empty($data['password'])) {
            $user->forceFill(['password' => $data['password'], 'remember_token' => Str::random(60)])->save();
            $request->session()->regenerate();
            $settings['password_changed_at'] = now()->toIso8601String();
        }
        $settings = array_replace($settings, array_intersect_key($data, array_flip(['email_notifications', 'platform_notifications'])));
        $state->gegevens = array_replace($state->gegevens ?? [], ['profile-settings' => $settings]);
        if (isset($state->gegevens['notification-settings'])) {
            $notifications = array_replace($state->gegevens['notification-settings'], array_intersect_key($data, array_flip(['email_notifications','platform_notifications'])));
            $state->gegevens = array_replace($state->gegevens, ['notification-settings'=>$notifications]);
        }
        $state->save();

        return response()->json(['message' => 'Je wijzigingen zijn opgeslagen.', 'passwordChanged' => !empty($data['password'])]);
    }
}
