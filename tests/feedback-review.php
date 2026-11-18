<?php

// Run with PHP 8.2+: php tests/feedback-review.php. All test writes are rolled back.
require dirname(__DIR__).'/vendor/autoload.php';
$app = require dirname(__DIR__).'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\Coach\FeedbackReviewController;
use App\Http\Controllers\Student\DataController;
use App\Models\Feedback;
use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

function check(bool $condition, string $message): void
{
    if (! $condition) throw new RuntimeException($message);
}
function reviewRequest(User $actor, mixed $hours): Request
{
    $request = Request::create('/coach/feedback/1/verwerkt', 'POST', ['hours' => $hours]);
    $request->setUserResolver(fn () => $actor);
    return $request;
}
function denied(callable $action, int $status): void
{
    try { $action(); } catch (HttpException $exception) {
        check($exception->getStatusCode() === $status, 'Unexpected rejection status');
        return;
    }
    throw new RuntimeException('Expected request rejection');
}

DB::beginTransaction();
try {
    $coach = User::where('rol', 'coach')->firstOrFail();
    $student = $coach->coachedGroups()->with('students')->get()->flatMap->students->firstWhere('rol', 'student');
    check($student !== null, 'A coach with an assigned demo student is required');
    $outcome = LearningOutcome::firstOrFail();
    $progress = Progress::updateOrCreate(['gebruiker_id' => $student->id, 'leeruitkomst_id' => $outcome->id],
        ['gevalideerde_uren' => 10, 'status' => 'in_ontwikkeling']);
    $request = Request::create('/mijn-feedback', 'POST', [
        'type' => 'received', 'person' => $coach->naam, 'title' => 'Test: feedback beoordelen', 'code' => $outcome->code,
        'positive' => 'Dit onderdeel is goed uitgevoerd.', 'improve' => 'De onderbouwing kan nog duidelijker.', 'action' => 'Werk de onderbouwing verder uit.',
    ]);
    $request->setUserResolver(fn () => $student);
    $response = app(DataController::class)->storeFeedback($request);
    $feedback = Feedback::findOrFail($response->getData(true)['id']);
    check(Feedback::reviewableBy($coach)->whereKey($feedback->id)->exists(), 'Student feedback must reach the assigned coach');
    $controller = app(FeedbackReviewController::class);
    $challenges = app(ChallengeService::class);
    foreach ([-1, 1.5, 10001, null, 'abc'] as $invalid) {
        try { $controller(reviewRequest($coach, $invalid), $feedback, $challenges); }
        catch (ValidationException) { continue; }
        throw new RuntimeException('Invalid hour value accepted');
    }
    $outsider = $coach->replicate();
    $outsider->email = 'feedback-test-'.bin2hex(random_bytes(5)).'@example.test';
    $outsider->studentnummer = null;
    $outsider->save();
    denied(fn () => $controller(reviewRequest($outsider, 3), $feedback, $challenges), 403);
    denied(fn () => $controller(reviewRequest($student, 3), $feedback, $challenges), 403);
    $controller(reviewRequest($coach, 3), $feedback, $challenges);
    check((int) $progress->fresh()->gevalideerde_uren === 13, 'Hour award must be additive');
    check($progress->fresh()->status === 'in_ontwikkeling', 'Review must preserve learning-outcome status');
    check($feedback->fresh()->awarded_hours === 3 && (int) $feedback->fresh()->reviewed_by === $coach->id, 'Review audit record missing');
    $controller(reviewRequest($coach, 99), $feedback, $challenges);
    check((int) $progress->fresh()->gevalideerde_uren === 13, 'Repeated submission duplicated hours');
    $xp = DB::table('activity_xp')->where('user_id', $student->id)->where('source', 'feedback')->where('source_id', $feedback->id);
    check($xp->count() === 1 && (int) $xp->sum('amount') === 40, 'Feedback XP must be granted exactly once');
    denied(fn () => app(DataController::class)->destroyFeedback($request, $feedback->fresh()), 422);

    $zero = $feedback->replicate(['processed_confirmed_at', 'awarded_hours', 'reviewed_by']);
    $zero->save();
    $controller(reviewRequest($coach, 0), $zero, $challenges);
    check($zero->fresh()->awarded_hours === 0 && (int) $progress->fresh()->gevalideerde_uren === 13, 'Zero-hour review failed');
    $failed = $zero->replicate(['processed_confirmed_at', 'awarded_hours', 'reviewed_by']);
    $failed->save();
    $failingSync = new class extends ChallengeService {
        public function sync(User $user): array { throw new RuntimeException('Simulated XP failure'); }
    };
    try { $controller(reviewRequest($coach, 2), $failed, $failingSync); }
    catch (RuntimeException $exception) { check($exception->getMessage() === 'Simulated XP failure', 'Unexpected failure'); }
    check($failed->fresh()->processed_confirmed_at === null && (int) $progress->fresh()->gevalideerde_uren === 13, 'A failed review must roll back both hours and confirmation');
    $progress->update(['gevalideerde_uren' => 10000]);
    try { $controller(reviewRequest($coach, 1), $failed, $challenges); throw new RuntimeException('Hour limit not enforced'); }
    catch (ValidationException) {}

    $given = $failed->replicate();
    $given->type = 'given';
    $given->save();
    denied(fn () => $controller(reviewRequest($coach, 1), $given, $challenges), 403);
    $newOutcome = $outcome->replicate();
    $newOutcome->code = 'FEEDBACK_TEST_'.bin2hex(random_bytes(5));
    $newOutcome->save();
    $missingProgress = $failed->replicate();
    $missingProgress->leeruitkomst_id = $newOutcome->id;
    $missingProgress->save();
    $controller(reviewRequest($coach, 2), $missingProgress, $challenges);
    $newProgress = Progress::where('gebruiker_id', $student->id)->where('leeruitkomst_id', $newOutcome->id)->firstOrFail();
    check((int) $newProgress->gevalideerde_uren === 2 && $newProgress->status === 'gestart', 'Missing progress must be created safely');

    Auth::login($coach);
    $coachRequest = reviewRequest($coach, 0);
    $view = app(App\Http\Controllers\Coach\DashboardController::class)->feedback($coachRequest);
    $html = $view->render();
    check(str_contains($html, 'Bevestigen en uren toekennen') && str_contains($html, 'Recent beoordeeld'), 'Review form or history missing');
    preg_match_all('/\bid="([^"]+)"/', $html, $matches);
    check(count($matches[1]) === count(array_unique($matches[1])), 'Duplicate field IDs');
    if (in_array('--fixtures', $argv, true)) {
        Illuminate\Support\Facades\URL::forceRootUrl('http://127.0.0.1:8001');
        file_put_contents(dirname(__DIR__).'/storage/logs/coach-source-settings.html', $view->render());
    }
    Auth::login($student);
    $studentView = app(App\Http\Controllers\Student\DashboardController::class)($request);
    $payload = collect($studentView->getData()['frontendData']['feedback'])->firstWhere('id', (string) $feedback->id);
    check($payload['awardedHours'] === 3 && $payload['confirmedAt'] !== null, 'Student cannot see awarded hours');
    echo "PASS: feedback linkage, validation, permissions, additive/zero awards, duplicate protection, XP, atomic rollback, hour limit, history and student visibility.\n";
} finally {
    DB::rollBack();
    echo "All test writes rolled back.\n";
}
