<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeeruitkomstController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        // Validate every required field before writing so incomplete outcomes cannot enter the catalog.
        $data = $this->validatedOutcome($request);

        LearningOutcome::create($data);

        return redirect()
            ->route('admin.dashboard', ['page' => 'leeruitkomsten'])
            ->with('status', 'De leeruitkomst is toegevoegd.');
    }

    public function update(Request $request, LearningOutcome $learningOutcome): RedirectResponse
    {
        // Ignore this record's own code when checking uniqueness, but reject codes used by other outcomes.
        $data = $this->validatedOutcome($request, $learningOutcome);

        $learningOutcome->update($data);

        return back()->with('status', 'De leeruitkomst is bijgewerkt.');
    }

    public function destroy(LearningOutcome $learningOutcome): RedirectResponse
    {
        // The database cascades deletion to linked progress and other dependent records.
        $learningOutcome->delete();

        return back()->with('status', 'De leeruitkomst is verwijderd.');
    }

    private function validatedOutcome(Request $request, ?LearningOutcome $learningOutcome = null): array
    {
        // Match validation limits to the database columns and require a real study phase to satisfy its foreign key.
        return $request->validate([
            'studiefase_id' => ['required', 'integer', Rule::exists('studiefases', 'id')],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('leeruitkomsten', 'code')->ignore($learningOutcome),
            ],
            'titel' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:45'],
            'omschrijving' => ['required', 'string'],
            'normuren' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
        ]);
    }
}
