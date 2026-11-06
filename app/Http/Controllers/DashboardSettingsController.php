<?php

namespace App\Http\Controllers;

use App\Models\UserState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardSettingsController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!in_array($user->rol, ['beheerder', 'teamleider'], true)) {
            abort(403);
        }

        $preferences = $request->validate([
            'compact' => ['required', 'boolean'],
            'darkMode' => ['required', 'boolean'],
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'density' => ['required', Rule::in(['compact', 'standard', 'spacious'])],
            'accent' => ['required', Rule::in(['teal', 'blue', 'purple', 'pink', 'orange', 'amber', 'green'])],
        ]);

        $state = UserState::firstOrNew(['gebruiker_id' => $user->id]);
        $state->gegevens = array_replace($state->gegevens ?? [], [
            'ontwikkeling-dashboard-instellingen' => $preferences,
        ]);
        $state->save();

        return response()->json(['saved' => true]);
    }
}
