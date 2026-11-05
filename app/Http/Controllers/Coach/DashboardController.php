<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Assignment;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends RoleDashboardController
{
    /** Toon het coachdashboard met de bijbehorende gegevens en bestanden. */
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return $this->dashboardView(
            user: $user,
            activeRole: 'coach',
            view: 'coach.dashboard',
            frontendData: $this->overviewData(),
            roleData: $this->students(),
            style: 'css/roles/coach.css',
            scripts: ['js/common.js', 'js/roles/coach.js'],
        );
    }

    /**
     * Verzamel de leeractiviteiten, doelen en projecten voor docentoverzichten.
     */
    private function overviewData(): array
    {
        $studentUsers = User::query()
            ->where('rol', 'student')
            ->get(['id', 'naam']);
        $studentIds = $studentUsers->pluck('id');

        // Vul de drie overzichten met gegevens die bij studenten horen.
        $activities = LearningOutcome::query()
            ->with('studyPhase')
            ->withCount([
                'progress as active_student_count' => fn ($query) => $query
                    ->whereIn('gebruiker_id', $studentIds)
                    ->whereIn('status', ['gestart', 'in_ontwikkeling']),
            ])
            ->orderBy('code')
            ->get()
            ->map(fn (LearningOutcome $outcome): array => [
                'code' => $outcome->code,
                'title' => $outcome->titel,
                'description' => $outcome->omschrijving,
                'phase' => $outcome->studyPhase?->naam,
                'activeStudentCount' => $outcome->active_student_count,
            ])
            ->all();

        $goals = LearningGoal::query()
            ->whereIn('gebruiker_id', $studentIds)
            ->with(['user', 'learningOutcome'])
            ->latest()
            ->get()
            ->map(fn (LearningGoal $goal): array => [
                'student' => $goal->user->naam,
                'title' => $goal->onderwerp,
                'description' => $goal->omschrijving,
                'status' => $goal->status,
                'outcome' => $goal->learningOutcome?->titel,
            ])
            ->all();

        $projects = Assignment::query()
            ->whereIn('gebruiker_id', $studentIds)
            ->with(['user', 'learningOutcomes'])
            ->latest()
            ->get()
            ->map(fn (Assignment $assignment): array => [
                'student' => $assignment->user->naam,
                'title' => $assignment->onderwerp,
                'type' => $assignment->type,
                'description' => $assignment->omschrijving,
                'status' => $assignment->status,
                'outcomes' => $assignment->learningOutcomes->pluck('titel')->all(),
                'dueDate' => $assignment->inleverdatum?->locale('nl')->translatedFormat('j F Y'),
            ])
            ->all();

        return [
            'activities' => $activities,
            'goals' => $goals,
            'projects' => $projects,
            'studentCount' => $studentUsers->count(),
        ];
    }

    /**
     * Geef de coach alleen gegevens die nodig zijn om studenten te begeleiden.
     */
    private function students(): array
    {
        return User::query()
            ->where('rol', 'student')
            ->with(['studyPhase', 'progress.learningOutcome'])
            ->orderBy('naam')
            ->get()
            ->map(fn (User $student): array => [
                'id' => $student->id,
                'name' => $student->naam,
                'phase' => $student->studyPhase?->naam,
                'hours' => $student->progress->sum('gevalideerde_uren'),
                // Zet actieve leeruitkomsten bovenaan, zodat lopend werk snel zichtbaar is.
                'outcomes' => $student->progress
                    ->sortBy(fn ($progress): int => in_array($progress->status, ['gestart', 'in_ontwikkeling'], true) ? 0 : 1)
                    ->map(fn ($progress): array => [
                        'id' => $progress->learningOutcome->id,
                        'title' => $progress->learningOutcome->titel,
                        'code' => $progress->learningOutcome->code,
                        'hours' => $progress->gevalideerde_uren,
                        'target' => $progress->normuren ?? $progress->learningOutcome->normuren,
                        'status' => $progress->status,
                    ])->values(),
            ])
            ->values()
            ->all();
    }
}
