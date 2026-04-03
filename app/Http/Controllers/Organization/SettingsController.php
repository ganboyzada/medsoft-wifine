<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('organization.settings.edit', [
            'organization' => $request->user()->organization,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'default_language' => ['required', 'in:'.implode(',', Organization::supportedLanguages())],
            'timezone' => ['required', 'string', 'max:80'],
        ]);

        $organization->update([
            'default_language' => $validated['default_language'],
            'timezone' => $validated['timezone'],
        ]);

        AuditTrail::record(
            action: 'organization.settings.updated',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $organization,
            payload: [
                'default_language' => $validated['default_language'],
                'timezone' => $validated['timezone'],
            ],
            request: $request
        );

        return redirect()
            ->route('organization.settings.edit')
            ->with('status', __('ui.settings_saved'));
    }
}
