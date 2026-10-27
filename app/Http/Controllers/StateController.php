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
            'archived-learning-outcomes',
            'hours-processing-read',
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
