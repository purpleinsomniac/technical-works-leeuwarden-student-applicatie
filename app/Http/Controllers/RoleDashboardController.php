<?php

namespace App\Http\Controllers;

use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Contracts\View\View;

abstract class RoleDashboardController extends Controller
{
    /**
     * Bouw de gedeelde gegevens voor de applicatielayout op.
     */
    protected function dashboardView(
        User $user,
        string $activeRole,
        string $view,
        array $frontendData = [],
        array $roleData = [],
        ?string $style = null,
        array $scripts = [],
        array $extraData = [],
    ): View {
        $user->loadMissing('appState');

        $initials = collect(preg_split('/\s+/', trim($user->naam)))
            ->filter()
            ->take(2)
            ->map(fn (string $namePart): string => mb_strtoupper(mb_substr($namePart, 0, 1)))
            ->implode('');

        return view($view, array_merge([
            'user' => $user,
            'applicationSettings' => $roleData['applicationSettings'] ?? ApplicationSetting::current(),
            'frontendUser' => [
                'id' => $user->id,
                'name' => $user->naam,
                'role' => $activeRole,
                'initials' => $initials,
            ],
            'frontendState' => $user->appState?->gegevens ?? [],
            'frontendData' => $frontendData,
            'roleData' => $roleData,
            'roleAssets' => [
                'style' => $style,
                'scripts' => $scripts,
            ],
        ], $extraData));
    }
}
