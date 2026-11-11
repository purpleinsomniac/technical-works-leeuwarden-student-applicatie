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
    ): View {
        $user->loadMissing('appState');
        $applicationSettings = $roleData['applicationSettings'] ?? ApplicationSetting::current();
        $personalSettings = $user->appState?->gegevens ?? [];
        $personalAppearance = is_array($personalSettings)
            ? ($personalSettings['ontwikkeling-dashboard-instellingen'] ?? [])
            : [];
        $personalAccent = is_array($personalAppearance) ? ($personalAppearance['accent'] ?? null) : null;
        $accentColors = [
            'teal' => '#009b87',
            'blue' => '#168bed',
            'purple' => '#6840e8',
            'pink' => '#df238b',
            'orange' => '#fa7900',
            'amber' => '#efb014',
            'green' => '#78b915',
        ];
        if (is_string($personalAccent) && isset($accentColors[$personalAccent])) {
            $personalAccent = $accentColors[$personalAccent];
        }
        $applicationAccentColor = is_string($personalAccent)
            && preg_match('/^#[0-9A-Fa-f]{6}$/D', $personalAccent) === 1
            ? strtolower($personalAccent)
            : $applicationSettings->application_accent_color;

        $initials = collect(preg_split('/\s+/', trim($user->naam)))
            ->filter()
            ->take(2)
            ->map(fn (string $namePart): string => mb_strtoupper(mb_substr($namePart, 0, 1)))
            ->implode('');

        return view($view, [
            'user' => $user,
            'applicationSettings' => $applicationSettings,
            'applicationAccentColor' => $applicationAccentColor,
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
        ]);
    }
}
