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

        if (isset($data['ontwikkeling-dashboard-instellingen'])) {
            $instellingen = $data['ontwikkeling-dashboard-instellingen'];
            $choices = ['theme'=>['light','dark','system'], 'accent'=>['teal','blue','purple','pink','orange','amber','green'], 'density'=>['compact','standard','spacious']];
            $toegestaneInstellingen = ['compact', 'darkMode', 'outcomes', 'goals', 'actions', 'feedback', 'deadlines', ...array_keys($choices)];

            if (! is_array($instellingen) || array_diff(array_keys($instellingen), $toegestaneInstellingen)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstellingen.']);
            }

            foreach ($instellingen as $key => $waarde) {
                if (isset($choices[$key]) ? !in_array($waarde, $choices[$key], true) : !is_bool($waarde)) {
                    throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstelling.']);
                }
            }
        }

        $outcomes = json_decode(file_get_contents(base_path('database/outcomes.json')), true);
        $validCodes = array_column($outcomes, 'code');

        foreach ($data['archived-learning-outcomes'] ?? [] as $code) {
            if (! in_array($code, $validCodes, true)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige leeruitkomst.']);
            }
        }

        $state = UserState::firstOrNew(['gebruiker_id' => $request->user()->id]);

        // Alleen de gewijzigde onderdelen vervangen. Andere voorkeuren blijven bewaard.
        $state->gegevens = array_replace($state->gegevens ?? [], $data);
        if (isset($data['notification-settings'])) {
            $profile = array_replace($state->gegevens['profile-settings'] ?? [], array_intersect_key($data['notification-settings'], array_flip(['email_notifications','platform_notifications'])));
            $state->gegevens = array_replace($state->gegevens, ['profile-settings'=>$profile]);
        }
        $state->save();

        return response()->json(['saved' => true]);
    }
}
