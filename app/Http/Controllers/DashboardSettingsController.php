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
        $sections = match ($user->rol) {
            'beheerder' => ['overview', 'users', 'learningOutcomes', 'applicationSettings'],
            'teamleider' => ['groups', 'reports'],
            default => abort(403),
        };

        $preferences = $request->validate([
            'compact' => ['required', 'boolean'],
            'darkMode' => ['required', 'boolean'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*' => ['required', 'string', 'distinct', Rule::in($sections)],
        ]);

        $state = UserState::firstOrNew(['gebruiker_id' => $user->id]);
        $state->gegevens = array_replace($state->gegevens ?? [], [
            'ontwikkeling-dashboard-instellingen' => $preferences,
        ]);
        $state->save();

        return response()->json(['saved' => true]);
    }
}
