<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;

use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgressController extends Controller
{
    public function update(Request $request, User $student, LearningOutcome $learningOutcome): RedirectResponse|JsonResponse
    {
        // Alleen coaches en beheerders mogen de voortgang van een student aanpassen.
        abort_unless(in_array($request->user()->rol, ['coach', 'beheerder'], true), 403);
        abort_unless($student->rol === 'student', 404);

        // Controleer of uren en status binnen de toegestane waarden vallen.
        $data = $request->validate([
            'gevalideerde_uren' => ['required', 'integer', 'min:0', 'max:10000'],
            'status' => ['required', Rule::in(['niet_gestart', 'gestart', 'in_ontwikkeling', 'behaald'])],
        ]);

        // Per student en leeruitkomst bestaat één voortgangsregel; werk die bij of maak hem aan.
        Progress::updateOrCreate(
            ['gebruiker_id' => $student->id, 'leeruitkomst_id' => $learningOutcome->id],
            [
                'gevalideerde_uren' => $data['gevalideerde_uren'],
                'normwaarden' => $learningOutcome->normwaarden,
                'status' => $data['status'],
            ],
        );

        // AJAX-opslag blijft op de detailpagina; gewone formulierverzendingen blijven doorsturen.
        if ($request->expectsJson()) {
            return response()->json(['message' => 'De voortgang is bijgewerkt.']);
        }

        return back()->with('status', 'De voortgang is bijgewerkt.');
    }
}
