<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Assignment;
use App\Models\ApplicationSetting;
use App\Models\Group;
use App\Models\LearningOutcome;
use App\Models\Progress;
use App\Models\Role;
use App\Models\StudyPhase;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends RoleDashboardController
{
    public function __invoke(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        // Render the administrator dashboard with database totals and its role-specific navigation script.
        return $this->dashboardView(
            user: $user,
            activeRole: 'beheerder',
            view: 'admin.dashboard',
            roleData: $this->overview(),
            style: 'css/roles/beheerder.css',
            scripts: ['js/common.js', 'js/role-settings.js', 'js/roles/beheerder.js'],
        );
    }

    private function overview(): array
    {
        // Supply the view with the exact totals and latest update timestamp shown in its overview.
        return [
            'userCount' => User::count(),
            'studentCount' => User::where('rol', 'student')->count(),
            'groups' => Group::with(['coach', 'students'])
                ->orderBy('naam')
                ->orderBy('schooljaar')
                ->get(),
            'coaches' => User::canCoach()->orderBy('naam')->get(['id', 'naam']),
            'students' => User::where('rol', 'student')->orderBy('naam')->get(['id', 'naam', 'email', 'studentnummer', 'leerjaar']),
            'progressCount' => Progress::count(),
            'assignmentCount' => Assignment::count(),
            'outcomeCount' => LearningOutcome::count(),
            'lastProgressUpdate' => Progress::max('updated_at'),
            'users' => User::with(['primaryRole', 'groups'])->orderBy('naam')->get(),
            'roles' => Role::orderBy('naam')->get(),
            'learningOutcomes' => LearningOutcome::query()
                ->select('leeruitkomsten.*')
                ->join('studiefases', 'studiefases.id', '=', 'leeruitkomsten.studiefase_id')
                ->with('studyPhase')
                ->orderBy('studiefases.naam')
                ->orderBy('leeruitkomsten.code')
                ->get(),
            'studyPhases' => StudyPhase::orderBy('naam')->get(),
            'applicationSettings' => ApplicationSetting::current(),
        ];
    }
}
