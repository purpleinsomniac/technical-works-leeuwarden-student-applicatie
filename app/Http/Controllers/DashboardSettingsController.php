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
        $activeRole = $request->validate([
            'role' => ['required', 'string', Rule::in(['beheerder', 'teamleider', 'coach'])],
        ])['role'];
        abort_unless($user->canUseRole($activeRole), 403);

        $preferences = $request->validate([
            'compact' => ['required', 'boolean'],
            'darkMode' => ['required', 'boolean'],
            'theme' => ['required', Rule::in(['light', 'dark', 'system'])],
            'density' => ['required', Rule::in(['compact', 'standard', 'spacious'])],
            'accent' => ['required', 'regex:/^(#[0-9A-Fa-f]{6}|teal|blue|purple|pink|orange|amber|green)$/D'],
            'sections' => ['sometimes', 'array', 'min:1'],
            'sections.*' => ['required', 'string', 'distinct', Rule::in(match ($activeRole) {
                'beheerder' => ['overview', 'users', 'classes', 'learningOutcomes', 'applicationSettings'],
                'teamleider' => ['groups', 'reports'],
                'coach' => ['coachOverview', 'coachStudents', 'coachActivities', 'coachGoals', 'coachProjects'],
            })],
        ]);

        $state = UserState::firstOrNew(['gebruiker_id' => $user->id]);
        $state->gegevens = array_replace($state->gegevens ?? [], [
            'ontwikkeling-dashboard-instellingen' => $preferences,
        ]);
        $state->save();

        return response()->json(['saved' => true]);
    }
}
