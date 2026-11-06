<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\Assignment;
use App\Models\AssignmentFile;
use App\Models\ApplicationSetting;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Reflection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class DataController extends Controller
{
    public function storeReflection(Request $request, ?Reflection $reflection = null): JsonResponse
    {
        $this->requireStudent($request);
        if ($reflection?->exists) abort_unless($reflection->gebruiker_id === $request->user()->id, 403);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'assignmentId' => ['nullable', 'integer', Rule::exists('opdrachten', 'id')->where('gebruiker_id', $request->user()->id)],
            'code' => ['required', 'string', Rule::exists('leeruitkomsten', 'code')],
            'situation' => ['required', 'string', 'min:10', 'max:3000'],
            'good' => ['required', 'string', 'min:10', 'max:3000'],
            'improve' => ['required', 'string', 'min:10', 'max:3000'],
            'next' => ['required', 'string', 'min:10', 'max:3000'],
        ]);
        $outcome = LearningOutcome::where('code', $data['code'])->firstOrFail();
        if (!empty($data['assignmentId'])) {
            $project = $request->user()->assignments()->with('learningOutcomes')->findOrFail($data['assignmentId']);
            abort_unless($project->learningOutcomes->contains('id',$outcome->id) && $project->learningOutcomes->sortBy('id')->first()?->studiefase_id === $outcome->studiefase_id, 422, 'Kies een leeruitkomst die bij dit project hoort.');
        }

        $editing = $reflection?->exists;
        $reflection = ($reflection ?? new Reflection())->fill([
            'opdracht_id' => $data['assignmentId'] ?? null,
            'gebruiker_id' => $request->user()->id,
            'leeruitkomst_id' => $outcome->id,
            'onderwerp' => $data['title'],
            'situatie' => $data['situation'],
            'wat_ging_goed' => $data['good'],
            'wat_kan_beter' => $data['improve'],
            'volgende_stap' => $data['next'],
        ]);
        $reflection->save();

        return response()->json($this->reflectionPayload($reflection->load('learningOutcome')), $editing ? 200 : 201);
    }

    public function destroyReflection(Request $request, Reflection $reflection): JsonResponse
    {
        abort_unless($reflection->gebruiker_id === $request->user()->id, 403);
        $reflection->delete();

        return response()->json(['deleted' => true]);
    }

    public function storeFeedback(Request $request): JsonResponse
    {
        $this->requireStudent($request);
        $data = $request->validate([
            'type' => ['required', Rule::in(['given', 'received'])],
            'person' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', Rule::exists('leeruitkomsten', 'code')],
            'positive' => ['required', 'string', 'min:10', 'max:3000'],
            'improve' => ['required', 'string', 'min:10', 'max:3000'],
            'action' => ['required', 'string', 'min:10', 'max:3000'],
        ]);
        $outcome = LearningOutcome::where('code', $data['code'])->firstOrFail();

        $feedback = Feedback::create([
            'auteur_id' => $request->user()->id,
            'student_id' => $request->user()->id,
            'leeruitkomst_id' => $outcome->id,
            'type' => $data['type'],
            'persoon' => $data['person'],
            'onderwerp' => $data['title'],
            'wat_ging_goed' => $data['positive'],
            'wat_kan_beter' => $data['improve'],
            'volgende_stap' => $data['action'],
        ]);

        return response()->json($this->feedbackPayload($feedback->load(['author', 'learningOutcome'])), 201);
    }

    public function destroyFeedback(Request $request, Feedback $feedback): JsonResponse
    {
        abort_unless($feedback->student_id === $request->user()->id, 403);
        $feedback->delete();

        return response()->json(['deleted' => true]);
    }

    public function storeGoal(Request $request): JsonResponse
    {
        $this->requireStudent($request);
        $data = $request->validate([
            'description' => ['required', 'string', 'min:10', 'max:500'],
            'code' => ['required', 'string', Rule::exists('leeruitkomsten', 'code')],
        ]);
        $outcome = LearningOutcome::with('studyPhase')->where('code', $data['code'])->firstOrFail();

        $goal = LearningGoal::create([
            'gebruiker_id' => $request->user()->id,
            'leeruitkomst_id' => $outcome->id,
            'onderwerp' => str($data['description'])->limit(120),
            'omschrijving' => $data['description'],
            'status' => 'actief',
        ]);

        return response()->json([
            'id' => $goal->id,
            'title' => $goal->onderwerp,
            'description' => $goal->omschrijving,
            'status' => $goal->status,
            'code' => $outcome->code,
            'outcome' => $outcome->titel,
            'phaseCode' => match ($outcome->studyPhase?->naam) {
                'Verdiepingsfase' => 'VF',
                'Afrondingsfase' => 'AF',
                default => 'OF',
            },
            'phase' => $outcome->studyPhase?->naam,
        ], 201);
    }

    public function submitAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        $this->requireStudent($request);
        abort_unless($assignment->gebruiker_id === $request->user()->id, 403);
        abort_unless(in_array($assignment->status, ['active', 'concept'], true), 422);
        $assignment->update(['status' => 'review']);

        return response()->json(['id' => $assignment->id, 'status' => $assignment->status]);
    }

    public function reopenAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        $this->requireStudent($request);
        abort_unless($assignment->gebruiker_id === $request->user()->id, 403);
        abort_unless($assignment->status === 'review', 422);
        $assignment->update(['status' => 'active']);

        return response()->json(['id' => $assignment->id, 'status' => $assignment->status]);
    }

    public function storeAssignmentFile(Request $request, Assignment $assignment): JsonResponse
    {
        $this->requireStudent($request);
        abort_unless($assignment->gebruiker_id === $request->user()->id, 403);
        abort_unless(in_array($assignment->status, ['active', 'concept'], true), 422, 'Zet de opdracht eerst terug om bestanden toe te voegen.');

        $settings = ApplicationSetting::current();
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:'.($settings->max_upload_size_mb * 1024),
                'mimes:'.implode(',', $settings->allowed_upload_extensions),
            ],
        ]);

        $upload = $request->file('file');
        $directory = storage_path('app/private/opdrachten/'.$assignment->id);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($upload->getClientOriginalExtension());
        $storedName = (string) Str::uuid().($extension ? '.'.$extension : '');
        $upload->move($directory, $storedName);

        $file = $assignment->files()->create([
            'originele_naam' => $upload->getClientOriginalName(),
            'opslagpad' => 'opdrachten/'.$assignment->id.'/'.$storedName,
            'mime_type' => $upload->getClientMimeType(),
            'bestandsgrootte' => filesize($directory.DIRECTORY_SEPARATOR.$storedName),
        ]);

        return response()->json($this->assignmentFilePayload($file), 201);
    }

    public function downloadAssignmentFile(Request $request, AssignmentFile $assignmentFile)
    {
        $this->requireStudent($request);
        abort_unless($assignmentFile->assignment->gebruiker_id === $request->user()->id, 403);
        $path = storage_path('app/private/'.$assignmentFile->opslagpad);
        abort_unless(is_file($path), 404);

        return response()->download($path, $assignmentFile->originele_naam, [
            'Content-Type' => $assignmentFile->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function destroyAssignmentFile(Request $request, AssignmentFile $assignmentFile): JsonResponse
    {
        $this->requireStudent($request);
        abort_unless($assignmentFile->assignment->gebruiker_id === $request->user()->id, 403);
        abort_unless(in_array($assignmentFile->assignment->status, ['active', 'concept'], true), 422, 'Zet de opdracht eerst terug om bestanden te verwijderen.');

        $path = storage_path('app/private/'.$assignmentFile->opslagpad);
        if (is_file($path)) {
            unlink($path);
        }
        $assignmentFile->delete();

        return response()->json(['deleted' => true]);
    }

    private function requireStudent(Request $request): void
    {
        abort_unless($request->user()->rol === 'student', 403);
    }

    private function reflectionPayload(Reflection $reflection): array
    {
        return [
            'id' => (string) $reflection->id,
            'title' => $reflection->onderwerp,
            'assignmentId' => $reflection->opdracht_id,
            'code' => $reflection->learningOutcome->code,
            'situation' => $reflection->situatie,
            'good' => $reflection->wat_ging_goed,
            'improve' => $reflection->wat_kan_beter,
            'next' => $reflection->volgende_stap,
            'date' => $reflection->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
        ];
    }

    private function feedbackPayload(Feedback $feedback): array
    {
        return [
            'id' => (string) $feedback->id,
            'type' => $feedback->type,
            'person' => $feedback->persoon ?: $feedback->author->naam,
            'title' => $feedback->onderwerp,
            'code' => $feedback->learningOutcome->code,
            'positive' => $feedback->wat_ging_goed,
            'improve' => $feedback->wat_kan_beter,
            'action' => $feedback->volgende_stap,
            'date' => $feedback->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
        ];
    }

    private function assignmentFilePayload(AssignmentFile $file): array
    {
        return [
            'id' => $file->id,
            'name' => $file->originele_naam,
            'size' => $file->bestandsgrootte,
            'mime' => $file->mime_type,
            'downloadUrl' => route('assignments.files.download', $file),
            'uploadedAt' => $file->created_at->locale('nl')->translatedFormat('j F Y \o\m H:i'),
        ];
    }
}
