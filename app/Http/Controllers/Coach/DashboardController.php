<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\RoleDashboardController;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends RoleDashboardController
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        return $this->dashboardView(
            user: $user,
            activeRole: 'coach',
            view: 'coach.dashboard',
            roleData: $this->students(),
            style: 'css/roles/coach.css',
            scripts: ['js/common.js', 'js/roles/coach.js'],
        );
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
            ->map(fn ($student): array => [
                'id' => $student->id,
                'name' => $student->naam,
                'phase' => $student->studyPhase?->naam,
                'hours' => $student->progress->sum('gevalideerde_uren'),
                'outcomes' => $student->progress->map(fn ($progress): array => [
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
