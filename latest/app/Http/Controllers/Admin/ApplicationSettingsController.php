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
            'max_upload_size_mb' => ['required', 'integer', 'min:1', 'max:50'],
            'allowed_upload_extensions' => ['required', 'array', 'min:1'],
            'allowed_upload_extensions.*' => ['required', 'string', 'distinct', Rule::in(self::ALLOWED_EXTENSIONS)],
        ]);

        ApplicationSetting::current()->update([
            'max_upload_size_mb' => $data['max_upload_size_mb'],
            'allowed_upload_extensions' => array_values($data['allowed_upload_extensions']),
            'maintenance_mode' => $request->boolean('maintenance_mode'),
        ]);

        return redirect()
            ->route('admin.dashboard', ['page' => 'applicatie-instellingen'])
            ->with('status', 'Applicatie-instellingen opgeslagen.');
    }
}
