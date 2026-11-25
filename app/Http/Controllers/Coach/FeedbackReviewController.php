<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Progress;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FeedbackReviewController extends Controller
{
    public function __invoke(Request $request, Feedback $feedback, ChallengeService $challenges): RedirectResponse
    {
        $coach = $request->user();
        abort_unless($coach->canUseRole('coach') || $coach->canUseRole('beheerder'), 403);
        abort_unless(Feedback::reviewableBy($coach)->whereKey($feedback->id)->exists(), 403);
        $data = $request->validate([
            'hours' => ['required', 'integer', 'min:0', 'max:80', 'multiple_of:10'],
        ], ['hours' => 'Kies 0 (geen punten) of 10, 20, 30, 40, 50, 60, 70 of 80 punten.']);

        $changed = DB::transaction(function () use ($feedback, $coach, $data, $challenges): bool {
            // Serialize awards for this student, including their XP synchronization.
            $student = User::whereKey($feedback->student_id)->lockForUpdate()->firstOrFail();
            abort_unless($student->rol === 'student', 422);
            $review = Feedback::whereKey($feedback->id)->lockForUpdate()->firstOrFail();
            if ($review->processed_confirmed_at) {
                return false;
            }
            $hours = (int) $data['hours'];
            $progress = Progress::where('gebruiker_id', $student->id)
                ->where('leeruitkomst_id', $review->leeruitkomst_id)->lockForUpdate()->first();
            if ((int) ($progress?->gevalideerde_uren ?? 0) + $hours > 10000) {
                throw ValidationException::withMessages(['hours' => 'Het totaal voor deze leeruitkomst mag niet hoger worden dan 10.000 uur.']);
            }
            if ($hours > 0) {
                if (! $progress) {
                    $progress = Progress::create([
                        'gebruiker_id' => $student->id,
                        'leeruitkomst_id' => $review->leeruitkomst_id,
                        'normwaarden' => $review->learningOutcome->normwaarden,
                        'gevalideerde_uren' => 0,
                        'status' => 'gestart',
                    ]);
                }
                $progress->increment('gevalideerde_uren', $hours);
                if ($progress->status === 'niet_gestart') {
                    $progress->update(['status' => 'gestart']);
                }
            }
            $review->forceFill([
                'awarded_hours' => $hours,
                'reviewed_by' => $coach->id,
                'processed_confirmed_at' => now(),
            ])->save();
            $challenges->sync($student);

            return true;
        });

        return back()->with('status', $changed
            ? 'Feedback bevestigd. '.(int) $data['hours'].' extra uur toegekend aan de leeruitkomst.'
            : 'Deze feedback is al beoordeeld. Er zijn geen extra uren toegekend.');
    }
}
