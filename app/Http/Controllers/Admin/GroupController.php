<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GroupController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedGroup($request);

        DB::transaction(function () use ($data): void {
            $group = Group::create([
                'naam' => $data['naam'],
                'schooljaar' => $data['schooljaar'] ?? null,
                'omschrijving' => $data['omschrijving'] ?? null,
                'coach_id' => $data['coach_id'] ?? null,
            ]);
            $group->students()->sync($data['student_ids'] ?? []);
        });

        return $this->groupsPage()->with('status', 'De groep is aangemaakt.');
    }

    public function update(Request $request, Group $group): RedirectResponse
    {
        $data = $this->validatedGroup($request);

        DB::transaction(function () use ($group, $data): void {
            $group->update([
                'naam' => $data['naam'],
                'schooljaar' => $data['schooljaar'] ?? null,
                'omschrijving' => $data['omschrijving'] ?? null,
                'coach_id' => $data['coach_id'] ?? null,
            ]);
            $group->students()->sync($data['student_ids'] ?? []);
        });

        return $this->groupsPage()->with('status', 'De groep en de gekoppelde studenten zijn bijgewerkt.');
    }

    public function destroy(Group $group): RedirectResponse
    {
        $group->delete();

        return $this->groupsPage()->with('status', 'De groep is verwijderd. Studentaccounts blijven behouden.');
    }

    private function validatedGroup(Request $request): array
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

    private function groupsPage(): RedirectResponse
    {
        return redirect()->route('admin.dashboard', ['page' => 'groepen']);
    }
}
