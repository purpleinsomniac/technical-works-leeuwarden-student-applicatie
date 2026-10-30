<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

use App\Models\Assignment;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\Reflection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataController extends Controller
{
    public function storeReflection(Request $request): JsonResponse
    {
        $this->requireStudent($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', Rule::exists('leeruitkomsten', 'code')],
            'situation' => ['required', 'string', 'min:10', 'max:3000'],
            'good' => ['required', 'string', 'min:10', 'max:3000'],
            'improve' => ['required', 'string', 'min:10', 'max:3000'],
            'next' => ['required', 'string', 'min:10', 'max:3000'],
        ]);
        $outcome = LearningOutcome::where('code', $data['code'])->firstOrFail();

        $reflection = Reflection::create([
            'gebruiker_id' => $request->user()->id,
            'leeruitkomst_id' => $outcome->id,
            'onderwerp' => $data['title'],
            'situatie' => $data['situation'],
            'wat_ging_goed' => $data['good'],
            'wat_kan_beter' => $data['improve'],
            'volgende_stap' => $data['next'],
        ]);

        return response()->json($this->reflectionPayload($reflection->load('learningOutcome')), 201);
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
            'description' => ['required', 'string', 'min:10', 'max:2000'],
            'code' => ['required', 'string', Rule::exists('leeruitkomsten', 'code')],
        ]);
        $outcome = LearningOutcome::where('code', $data['code'])->firstOrFail();

        LearningGoal::where('gebruiker_id', $request->user()->id)
            ->where('status', 'actief')
            ->update(['status' => 'afgerond']);

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

    private function requireStudent(Request $request): void
    {
        abort_unless($request->user()->rol === 'student', 403);
    }

    private function reflectionPayload(Reflection $reflection): array
    {
        return [
            'id' => (string) $reflection->id,
            'title' => $reflection->onderwerp,
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
}
