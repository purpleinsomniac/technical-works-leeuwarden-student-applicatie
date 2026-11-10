<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApplicationSettingsController extends Controller
{
    private const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'jpg', 'jpeg', 'png'];

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'application_accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/D'],
            'application_style' => ['required', Rule::in(['standard', 'rounded', 'sharp'])],
            'max_upload_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'allowed_upload_extensions' => ['required', 'array', 'min:1'],
            'allowed_upload_extensions.*' => ['required', 'string', 'distinct', Rule::in(self::ALLOWED_EXTENSIONS)],
        ]);

        ApplicationSetting::current()->update([
            'application_accent_color' => strtolower($data['application_accent_color']),
            'application_style' => $data['application_style'],
            'max_upload_size_mb' => $data['max_upload_size_mb'],
            'allowed_upload_extensions' => array_values($data['allowed_upload_extensions']),
            'maintenance_mode' => $request->boolean('maintenance_mode'),
        ]);

        return redirect()
            ->route('admin.dashboard', ['page' => 'applicatie-instellingen'])
            ->with('status', 'Applicatie-instellingen opgeslagen.');
    }
}
