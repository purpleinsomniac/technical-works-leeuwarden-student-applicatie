<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\UserState;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StateController extends Controller
{
    public function update(Request $request)
    {
        abort_unless($request->user()->rol === 'student', 403);

        $payload = $request->validate([
            'data' => ['required', 'array'],
        ]);

        $allowedKeys = [
            'notification-settings',
            'weekly-plan',
            'archived-learning-outcomes',
            'hours-processing-latest-read',
            'ontwikkeling-dashboard-instellingen',
        ];
        $data = array_intersect_key($payload['data'], array_flip($allowedKeys));
        if (isset($data['notification-settings'])) {
            $data['notification-settings'] = validator($data['notification-settings'], \App\Http\Controllers\ProfileController::notificationRules())->validate();
        }
        if (isset($data['weekly-plan'])) {
            validator(['tasks'=>$data['weekly-plan']], [
                'tasks'=>['array','max:250'],
                'tasks.*.id'=>['required','string','max:80','distinct'],
                'tasks.*.week'=>['required','date_format:Y-m-d'],
                'tasks.*.text'=>['required','string','max:200'],
                'tasks.*.done'=>['required','boolean'],
            ])->validate();
        }

        $collectionKeys = ['archived-learning-outcomes'];

        foreach ($collectionKeys as $key) {
            if (isset($data[$key]) && (! is_array($data[$key]) || count($data[$key]) > 250)) {
                throw ValidationException::withMessages([
                    'data' => 'Ongeldige of te grote gegevensverzameling.',
                ]);
            }
        }

        $state = UserState::firstOrNew(['gebruiker_id' => $request->user()->id]);
        if (isset($data['ontwikkeling-dashboard-instellingen'])) {
            $instellingen = $data['ontwikkeling-dashboard-instellingen'];
            $choices = ['theme'=>['light','dark','system'], 'accent'=>['teal','blue','purple','pink','orange','amber','green'], 'density'=>['compact','standard','spacious']];
            $toegestaneInstellingen = ['compact', 'darkMode', 'outcomes', 'goals', 'actions', 'feedback', 'deadlines', 'accent_changed', ...array_keys($choices)];

            if (! is_array($instellingen) || array_diff(array_keys($instellingen), $toegestaneInstellingen)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstellingen.']);
            }

            foreach ($instellingen as $key => $waarde) {
                $isValidAccent = $key === 'accent'
                    && is_string($waarde)
                    && (in_array($waarde, $choices['accent'], true) || preg_match('/^#[0-9A-Fa-f]{6}$/D', $waarde) === 1);
                $isValidChoice = isset($choices[$key]) && in_array($waarde, $choices[$key], true);
                if (! ($isValidAccent || $isValidChoice || (! isset($choices[$key]) && is_bool($waarde)))) {
                    throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstelling.']);
                }
            }

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

        $outcomes = json_decode(file_get_contents(base_path('database/outcomes.json')), true);
        $validCodes = array_column($outcomes, 'code');

        foreach ($data['archived-learning-outcomes'] ?? [] as $code) {
            if (! in_array($code, $validCodes, true)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige leeruitkomst.']);
            }
        }

        // Alleen de gewijzigde onderdelen vervangen. Andere voorkeuren blijven bewaard.
        $state->gegevens = array_replace($state->gegevens ?? [], $data);
        if (isset($data['notification-settings'])) {
            $profile = array_replace($state->gegevens['profile-settings'] ?? [], array_intersect_key($data['notification-settings'], array_flip(['email_notifications','platform_notifications'])));
            $state->gegevens = array_replace($state->gegevens, ['profile-settings'=>$profile]);
        }
        $state->save();

        return response()->json(['saved' => true, 'data' => $data]);
    }
}
