<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use App\Models\Assignment;
use App\Models\AssignmentFile;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Reflection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DataController extends Controller
{
    // Gebruik dezelfde validatie voor nieuwe en bestaande reflecties.
    public function storeReflection(Request $request, ?Reflection $reflection = null): JsonResponse
    {
        $this->requireStudent($request);
        $editing = $reflection?->exists;
        if ($editing) {
            abort_unless($reflection->gebruiker_id === $request->user()->id, 403);
        }

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
        if (! empty($data['assignmentId'])) {
            $project = $request->user()->assignments()->with('learningOutcomes')->findOrFail($data['assignmentId']);
            // Net als in het projectoverzicht bepaalt de eerste gekoppelde leeruitkomst de fase.
            $projectPhaseId = $project->learningOutcomes->sortBy('id')->first()?->studiefase_id;
            abort_unless(
                $project->learningOutcomes->contains('id', $outcome->id) && $projectPhaseId === $outcome->studiefase_id,
                422,
                'Kies een leeruitkomst die bij dit project hoort.',
            );
        }

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

        return response()->json(StudentPayload::reflection($reflection->load('learningOutcome')), $editing ? 200 : 201);
    }

    public function destroyReflection(Request $request, Reflection $reflection): JsonResponse
    {
        abort_unless($reflection->gebruiker_id === $request->user()->id, 403);
        $reflection->delete();

        return response()->json(['deleted' => true]);
    }

    // Sla feedback voor de ingelogde student op bij de gekozen leeruitkomst.
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

        return response()->json(StudentPayload::feedback($feedback->load(['author', 'learningOutcome'])), 201);
    }

    public function destroyFeedback(Request $request, Feedback $feedback): JsonResponse
    {
        abort_unless($feedback->student_id === $request->user()->id, 403);
        // Controleer de beoordeling onder een lock, zodat verwijderen en beoordelen niet tegelijk slagen.
        DB::transaction(function () use ($feedback): void {
            $record = Feedback::whereKey($feedback->id)->lockForUpdate()->firstOrFail();
            abort_if($record->processed_confirmed_at !== null, 422, 'Beoordeelde feedback kan niet worden verwijderd omdat er uren of XP aan gekoppeld zijn.');
            $record->delete();
        });

        return response()->json(['deleted' => true]);
    }

    // Maak een actief leerdoel en gebruik het begin van de omschrijving als titel.
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

        return response()->json(StudentPayload::goal($goal->setRelation('learningOutcome', $outcome)), 201);
    }

    // Zet een eigen project klaar voor beoordeling als de huidige status dit toelaat.
    public function submitAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        $this->requireStudent($request);
        abort_unless($assignment->gebruiker_id === $request->user()->id, 403);
        abort_unless(in_array($assignment->status, ['active', 'concept'], true), 422);
        $assignment->update(['status' => 'review']);

        return response()->json(['id' => $assignment->id, 'status' => $assignment->status]);
    }

    // Maak een eigen project dat op beoordeling wacht weer bewerkbaar.
    public function reopenAssignment(Request $request, Assignment $assignment): JsonResponse
    {
        $this->requireStudent($request);
        abort_unless($assignment->gebruiker_id === $request->user()->id, 403);
        abort_unless($assignment->status === 'review', 422);
        $assignment->update(['status' => 'active']);

        return response()->json(['id' => $assignment->id, 'status' => $assignment->status]);
    }

    // Controleer het bestand met de ingestelde limieten voordat het wordt opgeslagen.
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

        // Gebruik een unieke opslagnaam en bewaar de originele naam voor de student.
        $extension = strtolower($upload->getClientOriginalExtension());
        $storedName = (string) Str::uuid().($extension ? '.'.$extension : '');
        $upload->move($directory, $storedName);

        $file = $assignment->files()->create([
            'originele_naam' => $upload->getClientOriginalName(),
            'opslagpad' => 'opdrachten/'.$assignment->id.'/'.$storedName,
            'mime_type' => $upload->getClientMimeType(),
            'bestandsgrootte' => filesize($directory.DIRECTORY_SEPARATOR.$storedName),
        ]);

        return response()->json(StudentPayload::assignmentFile($file), 201);
    }

    // Lever het privébestand pas na controle van de projecteigenaar.
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
}
