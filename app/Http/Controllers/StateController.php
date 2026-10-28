<?php

namespace App\Http\Controllers;

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
            'ontwikkeling-reflecties',
            'ontwikkeling-feedback',
            'ontwikkeling-opdracht-statussen',
            'archived-learning-outcomes',
            'hours-processing-read',
            'ontwikkeling-dashboard-instellingen',
        ];
        $data = array_intersect_key($payload['data'], array_flip($allowedKeys));

        $collectionKeys = [
            'ontwikkeling-reflecties',
            'ontwikkeling-feedback',
            'archived-learning-outcomes',
        ];

        foreach ($collectionKeys as $key) {
            if (isset($data[$key]) && (! is_array($data[$key]) || count($data[$key]) > 250)) {
                throw ValidationException::withMessages([
                    'data' => 'Ongeldige of te grote gegevensverzameling.',
                ]);
            }
        }

        if (isset($data['ontwikkeling-opdracht-statussen'])) {
            $statussen = $data['ontwikkeling-opdracht-statussen'];

            if (! is_array($statussen) || count($statussen) > 100) {
                throw ValidationException::withMessages(['data' => 'Ongeldige opdrachtstatussen.']);
            }

            foreach ($statussen as $opdrachtId => $status) {
                if (! ctype_digit((string) $opdrachtId) || ! in_array($status, ['active', 'review', 'completed'], true)) {
                    throw ValidationException::withMessages(['data' => 'Ongeldige opdrachtstatus.']);
                }
            }
        }

        if (isset($data['ontwikkeling-dashboard-instellingen'])) {
            $instellingen = $data['ontwikkeling-dashboard-instellingen'];
            $toegestaneInstellingen = ['compact', 'darkMode', 'outcomes', 'actions', 'feedback', 'deadlines'];

            if (! is_array($instellingen) || array_diff(array_keys($instellingen), $toegestaneInstellingen)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstellingen.']);
            }

            foreach ($instellingen as $waarde) {
                if (! is_bool($waarde)) {
                    throw ValidationException::withMessages(['data' => 'Ongeldige dashboardinstelling.']);
                }
            }
        }

        $outcomes = json_decode(file_get_contents(base_path('database/outcomes.json')), true);
        $validCodes = array_column($outcomes, 'code');

        foreach (['ontwikkeling-reflecties', 'ontwikkeling-feedback'] as $key) {
            foreach ($data[$key] ?? [] as $item) {
                $hasValidCode = is_array($item)
                    && isset($item['code'])
                    && in_array($item['code'], $validCodes, true);

                if (! $hasValidCode) {
                    throw ValidationException::withMessages(['data' => 'Ongeldige leeruitkomst.']);
                }
            }
        }

        foreach ($data['archived-learning-outcomes'] ?? [] as $code) {
            if (! in_array($code, $validCodes, true)) {
                throw ValidationException::withMessages(['data' => 'Ongeldige leeruitkomst.']);
            }
        }

        UserState::updateOrCreate(
            ['gebruiker_id' => $request->user()->id],
            ['gegevens' => $data],
        );

        return response()->json(['saved' => true]);
    }
}
