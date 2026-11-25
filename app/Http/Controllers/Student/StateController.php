<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProfileController;
use App\Models\UserState;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StateController extends Controller
{
    // Valideer en bewaar gewijzigde voorkeuren van de ingelogde student.
    public function update(Request $request): JsonResponse
    {
        abort_unless($request->user()->rol === 'student', 403);

        $payload = $request->validate([
            'data' => ['required', 'array'],
        ]);

        // Laat alleen bekende onderdelen van de studentinstellingen door.
        $allowedKeys = [
            'notification-settings',
            'weekly-plan',
            'archived-learning-outcomes',
            'hours-processing-latest-read',
            'ontwikkeling-dashboard-instellingen',
        ];
        $data = array_intersect_key($payload['data'], array_flip($allowedKeys));
        if (isset($data['notification-settings'])) {
            $data['notification-settings'] = validator(
                $data['notification-settings'],
                ProfileController::notificationRules(),
            )->validate();
        }
        if (isset($data['weekly-plan'])) {
            validator(['tasks' => $data['weekly-plan']], [
                'tasks' => ['array', 'max:250'],
                'tasks.*.id' => ['required', 'string', 'max:80', 'distinct'],
                'tasks.*.week' => ['required', 'date_format:Y-m-d'],
                'tasks.*.text' => ['required', 'string', 'max:200'],
                'tasks.*.done' => ['required', 'boolean'],
            ])->validate();
        }

        $archivedOutcomes = $data['archived-learning-outcomes'] ?? [];
        if (! is_array($archivedOutcomes) || count($archivedOutcomes) > 250) {
            throw ValidationException::withMessages([
                'data' => 'Ongeldige of te grote gegevensverzameling.',
            ]);
        }

        $state = UserState::firstOrNew(['gebruiker_id' => $request->user()->id]);
        if (isset($data['ontwikkeling-dashboard-instellingen'])) {
            $instellingen = $data['ontwikkeling-dashboard-instellingen'];
            $this->validateDashboardSettings($instellingen);

            $currentPreferences = $state->gegevens['ontwikkeling-dashboard-instellingen'] ?? [];
            $currentPreferences = is_array($currentPreferences) ? $currentPreferences : [];
            // Alleen een expliciete kleurwijziging krijgt een nieuwe persoonlijke versie.
            $accentChanged = ($instellingen['accent_changed'] ?? false) === true;
            unset($instellingen['accent_changed']);
            $instellingen['accent_updated_at'] = $accentChanged
                ? now()->valueOf()
                : ($currentPreferences['accent_updated_at'] ?? null);
            $data['ontwikkeling-dashboard-instellingen'] = $instellingen;
        }

        // Controleer of de archiefcodes bij bestaande leeruitkomsten horen.
        $outcomes = json_decode(file_get_contents(base_path('database/outcomes.json')), true);
        $validCodes = array_column($outcomes, 'code');

        foreach ($archivedOutcomes as $code) {
            if (! in_array($code, $validCodes, true)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige leeruitkomst.']);
            }
        }

        // Alleen de gewijzigde onderdelen vervangen. Andere voorkeuren blijven bewaard.
        $settings = array_replace($state->gegevens ?? [], $data);
        if (isset($data['notification-settings'])) {
            // Beide instellingsschermen gebruiken dezelfde voorkeuren voor meldingskanalen.
            $channels = array_intersect_key($data['notification-settings'], array_flip([
                'email_notifications', 'platform_notifications',
            ]));
            $settings['profile-settings'] = array_replace($settings['profile-settings'] ?? [], $channels);
        }
        $state->gegevens = $settings;
        $state->save();

        return response()->json(['saved' => true, 'data' => $data]);
    }

    // Controleer keuzelijsten, schakelaars en vrije accentkleuren.
    private function validateDashboardSettings(mixed $settings): void
    {
        $choices = [
            'theme' => ['light', 'dark', 'system'],
            'accent' => ['teal', 'blue', 'purple', 'pink', 'orange', 'amber', 'green'],
            'density' => ['compact', 'standard', 'spacious'],
        ];
        $allowedKeys = ['compact', 'darkMode', 'outcomes', 'goals', 'actions', 'feedback', 'deadlines', 'accent_changed', ...array_keys($choices)];

        if (! is_array($settings) || array_diff(array_keys($settings), $allowedKeys)) {
            throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstellingen.']);
        }

        foreach ($settings as $key => $value) {
            $valid = isset($choices[$key]) ? in_array($value, $choices[$key], true) : is_bool($value);
            $isCustomAccent = $key === 'accent' && is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/D', $value) === 1;
            if (! $valid && ! $isCustomAccent) {
                throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstelling.']);
            }
        }
    }
}
