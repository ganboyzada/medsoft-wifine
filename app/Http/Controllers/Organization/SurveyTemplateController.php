<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SurveyTemplate;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SurveyTemplateController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $templates = SurveyTemplate::query()
            ->where('organization_id', $organization->id)
            ->withCount('questions')
            ->latest()
            ->paginate(12);

        return view('organization.surveys.index', compact('organization', 'templates'));
    }

    public function create(Request $request): View
    {
        return view('organization.surveys.create', [
            'organization' => $request->user()->organization,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($validated['is_default'] ?? false) === true) {
            SurveyTemplate::query()
                ->where('organization_id', $organization->id)
                ->update(['is_default' => false]);
        }

        $template = SurveyTemplate::query()->create([
            'organization_id' => $organization->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        AuditTrail::record(
            action: 'organization.survey_template.created',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $template,
            request: $request
        );

        return redirect()
            ->route('organization.surveys.edit', $template)
            ->with('status', 'Survey template created. Add your custom questions below.');
    }

    public function show(Request $request, SurveyTemplate $survey): RedirectResponse
    {
        return redirect()->route('organization.surveys.edit', $survey);
    }

    public function edit(Request $request, SurveyTemplate $survey): View
    {
        $organization = $request->user()->organization;
        abort_if($survey->organization_id !== $organization->id, 404);

        $survey->load('questions');

        return view('organization.surveys.edit', [
            'organization' => $organization,
            'survey' => $survey,
        ]);
    }

    public function update(Request $request, SurveyTemplate $survey): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($survey->organization_id !== $organization->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (($validated['is_default'] ?? false) === true) {
            SurveyTemplate::query()
                ->where('organization_id', $organization->id)
                ->where('id', '!=', $survey->id)
                ->update(['is_default' => false]);
        }

        $survey->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        AuditTrail::record(
            action: 'organization.survey_template.updated',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $survey,
            request: $request
        );

        return redirect()
            ->route('organization.surveys.edit', $survey)
            ->with('status', 'Survey template updated.');
    }

    public function destroy(Request $request, SurveyTemplate $survey): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($survey->organization_id !== $organization->id, 404);

        $inUse = $survey->portals()->exists();

        if ($inUse) {
            return back()->withErrors([
                'template' => 'Cannot delete this template because it is assigned to one or more portals.',
            ]);
        }

        $survey->delete();

        AuditTrail::record(
            action: 'organization.survey_template.deleted',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $survey,
            request: $request
        );

        return redirect()
            ->route('organization.surveys.index')
            ->with('status', 'Survey template deleted.');
    }
}
