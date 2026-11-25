<?php

namespace App\Http\Controllers\Coach;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Assignment;
use App\Models\Group;
use App\Models\Feedback;
use App\Models\LearningGoal;
use App\Models\LearningOutcome;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends RoleDashboardController
{
    /** Toon het coachdashboard met de bijbehorende gegevens en bestanden. */
    public function __invoke(Request $request): View
    {
        return $this->renderCoachPage($request, 'overview');
    }

    public function groups(Request $request): View
    {
        return $this->renderCoachPage($request, 'groups');
    }

    public function group(Request $request, int $group): View
    {
        return $this->renderCoachPage($request, 'group', groupId: $group);
    }

    public function activities(Request $request): View
    {
        return $this->renderCoachPage($request, 'activities');
    }

    public function goals(Request $request): View
    {
        return $this->renderCoachPage($request, 'goals');
    }

    public function projects(Request $request): View
    {
        return $this->renderCoachPage($request, 'projects');
    }

    public function feedback(Request $request): View
    {
        return $this->renderCoachPage($request, 'feedback');
    }

    public function settings(Request $request): View
    {
        return $this->renderCoachPage($request, 'settings');
    }

    public function student(Request $request, int $student): View
    {
        return $this->renderCoachPage($request, 'student', $student);
    }

    private function renderCoachPage(Request $request, string $page, ?int $studentId = null, ?int $groupId = null): View
    {
        /** @var User $user */
        $user = $request->user();
        // An administrator using the coach view can still inspect every group.
        // Regular coaches remain limited to groups assigned to their own account.
        $groupsQuery = $user->canUseRole('beheerder')
            ? Group::query()
            : $user->coachedGroups();
        $groups = $groupsQuery
            ->with(['students' => fn ($query) => $query
                ->where('rol', 'student')
                ->with(['studyPhase', 'progress.learningOutcome'])])
            ->orderBy('naam')
            ->orderBy('schooljaar')
            ->get();
        $selectedGroup = $groupId === null ? null : $groups->firstWhere('id', $groupId);
        abort_if($groupId !== null && $selectedGroup === null, 404);
        $studentGroups = $selectedGroup ? collect([$selectedGroup]) : $groups;
        $students = $studentGroups
            ->flatMap(fn (Group $group) => $group->students)
            ->unique('id')
            ->sortBy('naam')
            ->values();
        $roleData = $this->students($students, $groups);

        if ($studentId !== null) {
            $student = collect($roleData)->firstWhere('id', $studentId);
            abort_unless($student, 404);
            $roleData = [$student];
        }

        return $this->dashboardView(
            user: $user,
            activeRole: 'coach',
            view: 'coach.dashboard',
            frontendData: $this->overviewData($user, $students, $groups),
            roleData: $roleData,
            style: 'css/roles/coach.css',
            scripts: ['js/common.js', 'js/role-settings.js', 'js/roles/coach.js'],
            extraData: [
                'coachPage' => $page,
                'coachStudentId' => $studentId,
                'coachGroup' => $selectedGroup ? [
                    'id' => $selectedGroup->id,
                    'name' => $selectedGroup->naam,
                    'schoolYear' => $selectedGroup->schooljaar,
                    'description' => $selectedGroup->omschrijving,
                ] : null,
            ],
        );
    }

    /**
     * Verzamel de leeractiviteiten, doelen en projecten voor docentoverzichten.
     */
    private function overviewData(User $coach, Collection $students, Collection $groups): array
    {
        $studentIds = $students->pluck('id');

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

        $coachStudentIds = $students->pluck('id')->flip();
        $goalStudents = User::query()
            ->where('rol', 'student')
            ->with(['learningGoals' => fn ($query) => $query->with('learningOutcome')->latest()])
            ->orderBy('naam')
            ->get(['id', 'naam'])
            ->map(function (User $student) use ($coachStudentIds): array {
                return [
                    'id' => $student->id,
                    'name' => $student->naam,
                    'canViewProgress' => $coachStudentIds->has($student->id),
                    'goals' => $student->learningGoals
                        ->map(fn (LearningGoal $goal): array => [
                            'studentId' => $student->id,
                            'student' => $student->naam,
                            'title' => $goal->onderwerp,
                            'description' => $goal->omschrijving,
                            'status' => $goal->status,
                            'outcome' => $goal->learningOutcome?->titel,
                        ])
                        ->all(),
                ];
            })
            ->all();
        $goals = collect($goalStudents)
            ->flatMap(fn (array $goalStudent): array => $goalStudent['goals'])
            ->all();

        $projectRecords = Assignment::query()
            ->whereIn('gebruiker_id', $studentIds)
            ->with(['user.studyPhase', 'learningOutcomes'])
            ->latest()
            ->get();
        $projects = $projectRecords
            ->map(fn (Assignment $assignment): array => [
                'id' => $assignment->id,
                'studentId' => $assignment->gebruiker_id,
                'student' => $assignment->user->naam,
                'phase' => $assignment->user->studyPhase?->naam,
                'title' => $assignment->onderwerp,
                'type' => $assignment->type,
                'description' => $assignment->omschrijving,
                'status' => $assignment->status,
                'statusLabel' => match ($assignment->status) {
                    'concept' => 'Concept',
                    'active' => 'In uitvoering',
                    'review' => 'Ter beoordeling',
                    'completed' => 'Afgerond',
                    default => str_replace('_', ' ', ucfirst($assignment->status)),
                },
                'outcomes' => $assignment->learningOutcomes->pluck('titel')->all(),
                'dueDate' => $assignment->inleverdatum?->locale('nl')->translatedFormat('j F Y'),
            ])
            ->all();

        $pendingProjects = $projectRecords
            ->where('status', 'review')
            ->sortBy('updated_at')
            ->map(fn (Assignment $assignment): array => [
                'id' => $assignment->id,
                'studentId' => $assignment->gebruiker_id,
                'student' => $assignment->user->naam,
                'phase' => $assignment->user->studyPhase?->naam,
                'title' => $assignment->onderwerp,
                'submittedAt' => $assignment->updated_at?->locale('nl')->translatedFormat('j M Y'),
            ])
            ->all();

        $pendingFeedback = Feedback::reviewableBy($coach)
            ->whereNull('processed_confirmed_at')
            ->with(['student:id,naam', 'learningOutcome'])
            ->oldest('updated_at')
            ->get()
            ->map(fn (Feedback $feedback): array => [
                'id' => $feedback->id,
                'student' => $feedback->student->naam,
                'title' => $feedback->onderwerp,
                'updatedAt' => $feedback->updated_at?->locale('nl')->translatedFormat('j M Y'),
                'outcome' => $feedback->learningOutcome->titel,
                'code' => $feedback->learningOutcome->code,
                'positive' => $feedback->wat_ging_goed,
                'improve' => $feedback->wat_kan_beter,
                'action' => $feedback->volgende_stap,
            ])
            ->all();

        $reviewedFeedback = Feedback::reviewableBy($coach)->whereNotNull('processed_confirmed_at')
            ->with(['student:id,naam', 'learningOutcome', 'reviewer:id,naam'])
            ->latest('processed_confirmed_at')->limit(10)->get()
            ->map(fn (Feedback $feedback): array => [
                'student' => $feedback->student->naam,
                'title' => $feedback->onderwerp,
                'outcome' => $feedback->learningOutcome->titel,
                'hours' => $feedback->awarded_hours,
                'reviewer' => $feedback->reviewer?->naam,
                'date' => $feedback->processed_confirmed_at->timezone('Europe/Berlin')->locale('nl')->translatedFormat('j M Y, H:i'),
            ])->all();

        return [
            'activities' => $activities,
            'goals' => $goals,
            'goalStudents' => $goalStudents,
            'projects' => $projects,
            'pendingProjects' => $pendingProjects,
            'pendingFeedback' => $pendingFeedback,
            'reviewedFeedback' => $reviewedFeedback,
            'groups' => $groups->map(fn (Group $group): array => [
                'id' => $group->id,
                'name' => $group->naam,
                'schoolYear' => $group->schooljaar,
                'description' => $group->omschrijving,
                'students' => $group->students
                    ->map(fn (User $student): array => [
                        'id' => $student->id,
                        'name' => $student->naam,
                        'studentNumber' => $student->studentnummer,
                        'year' => $student->leerjaar,
                        'phase' => $student->studyPhase?->naam,
                    ])->all(),
            ])->all(),
            'studentCount' => $students->count(),
        ];
    }

    /**
     * Geef de coach alleen gegevens die nodig zijn om studenten te begeleiden.
     */
    private function students(Collection $students, Collection $groups): array
    {
        $studentIds = $students->pluck('id');
        $groupMemberships = $groups
            ->flatMap(fn (Group $group) => $group->students->map(fn (User $student): array => [
                'studentId' => $student->id,
                'groupId' => $group->id,
                'groupName' => $group->naam,
            ]))
            ->groupBy('studentId');
        $activityXp = DB::table('activity_xp')
            ->whereIn('user_id', $studentIds)
            ->select('user_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
        $challengeXp = DB::table('xp_transactions')
            ->whereIn('user_id', $studentIds)
            ->select('user_id')
            ->selectRaw('SUM(amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');
        $outcomeXp = DB::table('activity_xp')
            ->whereIn('user_id', $studentIds)
            ->where('source', 'outcome')
            ->get()
            ->groupBy('user_id');
        // Creation dates describe student actions; coach edits are deliberately excluded.
        $latestActivity = collect();
        foreach ([
            [new Assignment(), 'gebruiker_id', 'Project aangemaakt'],
            [new \App\Models\Reflection(), 'gebruiker_id', 'Reflectie toegevoegd'],
            [new Feedback(), 'student_id', 'Feedback toegevoegd'],
        ] as [$model, $owner, $label]) {
            $query = $model->newQuery()->whereIn($owner, $studentIds)->whereNotNull('created_at');
            if ($model instanceof Feedback) {
                $query->whereColumn('auteur_id', 'student_id');
            }
            $latestActivity = $latestActivity->concat($query
                ->selectRaw($owner.' as student_id, MAX(created_at) as occurred_at')
                ->groupBy($owner)->get()
                ->map(fn ($record): array => [
                    'studentId' => (int) $record->student_id,
                    'label' => $label,
                    'at' => $record->occurred_at,
                ]));
        }
        $latestActivity = $latestActivity->sortByDesc('at')->unique('studentId')->keyBy('studentId');
        $levelNames = [
            'Nieuwkomer',
            'Ontdekker',
            'Aanpakker',
            'Probleemoplosser',
            'Projectbouwer',
            'Zelfstandig specialist',
            'Projectprofessional',
            'Innovator',
            'Expert',
            'Meester',
        ];

        return $students
            ->map(function (User $student) use ($activityXp, $challengeXp, $outcomeXp, $latestActivity, $levelNames, $groupMemberships): array {
                $xp = (int) ($activityXp[$student->id] ?? 0) + (int) ($challengeXp[$student->id] ?? 0);
                $level = intdiv($xp, 1000) + 1;
                $levelProgress = $xp % 1000;
                $memberships = $groupMemberships->get($student->id, collect());
                $activity = $latestActivity->get($student->id);
                $activityDate = $activity ? \Carbon\Carbon::parse($activity['at'])->timezone('Europe/Berlin') : null;
                $scores = app(\App\Services\LearningProgress::class)->snapshot($student)['outcomes'];
                $educationPercentage = \App\Services\StudentPointTargets::percentage(
                    array_sum(array_column($scores, 'earned')),
                    3 * \App\Services\StudentPointTargets::PHASE_TARGET,
                );

                return [
                    'id' => $student->id,
                    'educationPercentage' => $educationPercentage,
                    'name' => $student->naam,
                    'studentNumber' => $student->studentnummer,
                    'year' => $student->leerjaar,
                    'phase' => $student->studyPhase?->naam,
                    'lastActivity' => $activityDate ? [
                        'label' => $activity['label'],
                        'at' => $activityDate->toIso8601String(),
                        'date' => $activityDate->locale('nl')->translatedFormat('j M Y, H:i'),
                        'relative' => $activityDate->locale('nl')->diffForHumans(),
                    ] : null,
                    'hours' => $student->progress->sum('gevalideerde_uren'),
                    'xp' => $xp,
                    'activityXp' => (int) ($activityXp[$student->id] ?? 0),
                    'challengeXp' => (int) ($challengeXp[$student->id] ?? 0),
                    'level' => $level,
                    'levelName' => $levelNames[$level - 1] ?? 'Meester · rang '.($level - 9),
                    'levelProgress' => $levelProgress,
                    'levelProgressPercent' => $levelProgress / 10,
                    'groupIds' => $memberships->pluck('groupId')->all(),
                    'groupNames' => $memberships->pluck('groupName')->all(),
                    // Zet actieve leeruitkomsten bovenaan, zodat lopend werk snel zichtbaar is.
                    'outcomes' => $student->progress
                        ->sortBy(fn ($progress): int => in_array($progress->status, ['gestart', 'in_ontwikkeling'], true) ? 0 : 1)
                        ->map(fn ($progress): array => [
                            'id' => $progress->learningOutcome->id,
                            'title' => $progress->learningOutcome->titel,
                            'code' => $progress->learningOutcome->code,
                            'xp' => (int) $outcomeXp->get($student->id, collect())
                                ->where('source_id', $progress->learningOutcome->id)->sum('amount'),
                            'hours' => $progress->gevalideerde_uren,
                            'target' => $progress->normwaarden ?? $progress->learningOutcome->normwaarden,
                            'status' => $progress->status,
                        ])->values(),
                ];
            })
            ->values()
            ->all();
    }
}
