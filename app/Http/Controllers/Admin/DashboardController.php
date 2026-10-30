<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\RoleDashboardController;
use App\Models\Assignment;
use App\Models\LearningOutcome;
use App\Models\Progress;
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
            activeRole: 'beheerder',
            view: 'admin.dashboard',
            roleData: $this->overview(),
            scripts: ['js/common.js', 'js/roles/beheerder.js'],
        );
    }

    private function overview(): array
    {
        return [
            'userCount' => User::count(),
            'studentCount' => User::where('rol', 'student')->count(),
            'progressCount' => Progress::count(),
            'assignmentCount' => Assignment::count(),
            'outcomeCount' => LearningOutcome::count(),
            'lastProgressUpdate' => Progress::max('updated_at'),
        ];
    }
}
