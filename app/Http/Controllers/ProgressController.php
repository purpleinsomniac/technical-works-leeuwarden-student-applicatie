<?php

namespace App\Http\Controllers;

use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    public function update(Request $request, User $student, LearningOutcome $learningOutcome): RedirectResponse
    {
        abort_unless(in_array($request->user()->rol, ['coach', 'beheerder'], true), 403);
        abort_unless($student->rol === 'student', 404);
        $data = $request->validate([
            'gevalideerde_uren' => ['required', 'integer', 'min:0', 'max:10000'],
            'status' => ['required', Rule::in(['niet_gestart', 'gestart', 'in_ontwikkeling', 'behaald'])],
        ]);

        Progress::updateOrCreate(
            ['gebruiker_id' => $student->id, 'leeruitkomst_id' => $learningOutcome->id],
            [
                'gevalideerde_uren' => $data['gevalideerde_uren'],
                'normuren' => $learningOutcome->normuren,
                'status' => $data['status'],
            ],
        );

        return back()->with('status', 'De voortgang is bijgewerkt.');
    }
}
