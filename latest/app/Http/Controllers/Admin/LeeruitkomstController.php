<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LearningOutcome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LeeruitkomstController extends Controller
{
    public function update(Request $request, LearningOutcome $learningOutcome): RedirectResponse
    {
        $data = $request->validate([
            'normwaarden' => ['nullable', 'string', 'max:10000'],
        ]);
        $learningOutcome->update([
            'normwaarden' => $data['normwaarden'] ?? null,
        ]);

        return redirect()
            ->route('admin.dashboard', ['page' => 'leeruitkomsten'])
            ->with('status', 'De leeruitkomst is bijgewerkt.');
    }
}
