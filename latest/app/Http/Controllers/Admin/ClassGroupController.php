<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClassGroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedClass($request);

        DB::transaction(function () use ($data): void {
            $classGroup = ClassGroup::create([
                'naam' => $data['naam'],
                'schooljaar' => $data['schooljaar'] ?? null,
                'omschrijving' => $data['omschrijving'] ?? null,
                'coach_id' => $data['coach_id'] ?? null,
            ]);
            $classGroup->students()->sync($data['student_ids'] ?? []);
        });

        return $this->classesPage()->with('status', 'De klas is aangemaakt.');
    }

    public function update(Request $request, ClassGroup $classGroup): RedirectResponse
    {
        $data = $this->validatedClass($request);

        DB::transaction(function () use ($classGroup, $data): void {
            $classGroup->update([
                'naam' => $data['naam'],
                'schooljaar' => $data['schooljaar'] ?? null,
                'omschrijving' => $data['omschrijving'] ?? null,
                'coach_id' => $data['coach_id'] ?? null,
            ]);
            $classGroup->students()->sync($data['student_ids'] ?? []);
        });

        return $this->classesPage()->with('status', 'De klas en de gekoppelde studenten zijn bijgewerkt.');
    }

    public function destroy(ClassGroup $classGroup): RedirectResponse
    {
        $classGroup->delete();

        return $this->classesPage()->with('status', 'De klas is verwijderd. Studentaccounts blijven behouden.');
    }

    private function validatedClass(Request $request): array
    {
        return $request->validate([
            'naam' => ['required', 'string', 'max:120'],
            'schooljaar' => ['nullable', 'string', 'max:20'],
            'omschrijving' => ['nullable', 'string', 'max:2000'],
            'coach_id' => [
                'nullable',
                'integer',
                Rule::exists('gebruikers', 'id')->where('rol', 'coach'),
            ],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('gebruikers', 'id')->where('rol', 'student'),
            ],
        ]);
    }

    private function classesPage(): RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['page' => 'klassen']);
    }
}
