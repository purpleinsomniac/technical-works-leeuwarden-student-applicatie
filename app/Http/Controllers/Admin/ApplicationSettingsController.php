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
            'max_upload_size_mb' => ['sometimes', 'required', 'integer', 'min:1', 'max:50'],
            'allowed_upload_extensions' => ['sometimes', 'required', 'array', 'min:1'],
            'allowed_upload_extensions.*' => ['required', 'string', 'distinct', Rule::in(self::ALLOWED_EXTENSIONS)],
        ]);

        $settings = ApplicationSetting::current();
        $accentColor = strtolower($data['application_accent_color']);
        $updates = [
            'application_accent_color' => $accentColor,
            'application_style' => $data['application_style'],
            'maintenance_mode' => $request->boolean('maintenance_mode'),
        ];
        if (array_key_exists('max_upload_size_mb', $data)) {
            $updates['max_upload_size_mb'] = $data['max_upload_size_mb'];
        }
        if (array_key_exists('allowed_upload_extensions', $data)) {
            $updates['allowed_upload_extensions'] = array_values($data['allowed_upload_extensions']);
        }
        // Alleen een daadwerkelijk gewijzigde globale kleur krijgt een nieuwere versie.
        if ($settings->application_accent_color !== $accentColor) {
            $updates['application_accent_updated_at'] = now()->valueOf();
        }
        $settings->update($updates);

        return redirect()
            ->route('admin.dashboard', ['page' => 'applicatie-instellingen'])
            ->with('status', 'Applicatie-instellingen opgeslagen.');
    }
}
