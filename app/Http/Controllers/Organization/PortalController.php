<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SurveyTemplate;
use App\Models\WifiPortal;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $portals = WifiPortal::query()
            ->where('organization_id', $organization->id)
            ->with('surveyTemplate')
            ->latest()
            ->paginate(12);

        return view('organization.portals.index', compact('organization', 'portals'));
    }

    public function create(Request $request): View
    {
        $organization = $request->user()->organization;

        $templates = SurveyTemplate::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('organization.portals.create', compact('organization', 'templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:120', 'unique:wifi_portals,slug'],
            'survey_template_id' => ['nullable', 'integer', 'exists:survey_templates,id'],
            'welcome_title' => ['required', 'string', 'max:160'],
            'welcome_text' => ['nullable', 'string', 'max:2000'],
            'terms_text' => ['nullable', 'string', 'max:4000'],
            'network_vendor' => ['required', 'string', 'max:50'],
            'session_ttl_minutes' => ['required', 'integer', 'min:30', 'max:1440'],
            'post_login_redirect_url' => ['nullable', 'url', 'max:255'],
            'require_marketing_consent' => ['nullable', 'boolean'],
            'logo_override' => ['nullable', 'image', 'max:2048'],
        ]);

        if (! empty($validated['survey_template_id'])) {
            $templateOwned = SurveyTemplate::query()
                ->where('organization_id', $organization->id)
                ->whereKey($validated['survey_template_id'])
                ->exists();

            abort_if(! $templateOwned, 422, 'Selected template does not belong to your organization.');
        }

        $portal = WifiPortal::query()->create([
            'organization_id' => $organization->id,
            'survey_template_id' => $validated['survey_template_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name'].'-'.Str::random(4)),
            'welcome_title' => $validated['welcome_title'],
            'welcome_text' => $validated['welcome_text'] ?? null,
            'terms_text' => $validated['terms_text'] ?? null,
            'network_vendor' => $validated['network_vendor'],
            'session_ttl_minutes' => $validated['session_ttl_minutes'],
            'post_login_redirect_url' => $validated['post_login_redirect_url'] ?? null,
            'require_marketing_consent' => (bool) ($validated['require_marketing_consent'] ?? false),
        ]);

        if ($request->hasFile('logo_override')) {
            $portal->update([
                'logo_override_path' => $request->file('logo_override')->store('portal-logos', 'public'),
            ]);
        }

        AuditTrail::record(
            action: 'organization.portal.created',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $portal,
            request: $request
        );

        return redirect()
            ->route('organization.portals.show', $portal)
            ->with('status', 'Portal created. Keep the integration key/secret safe for network integration.')
            ->with('integration_credentials', [
                'portal_key' => $portal->integration_key,
                'portal_secret' => $portal->integration_secret,
            ]);
    }

    public function show(Request $request, WifiPortal $portal): View
    {
        $organization = $request->user()->organization;
        abort_if($portal->organization_id !== $organization->id, 404);

        $portal->load(['surveyTemplate.questions', 'sessions' => fn ($query) => $query->latest()->limit(20)]);

        $templates = SurveyTemplate::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('organization.portals.show', compact('organization', 'portal', 'templates'));
    }

    public function edit(Request $request, WifiPortal $portal): View
    {
        $organization = $request->user()->organization;
        abort_if($portal->organization_id !== $organization->id, 404);

        $templates = SurveyTemplate::query()
            ->where('organization_id', $organization->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('organization.portals.edit', compact('organization', 'portal', 'templates'));
    }

    public function update(Request $request, WifiPortal $portal): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($portal->organization_id !== $organization->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'alpha_dash', 'max:120', 'unique:wifi_portals,slug,'.$portal->id],
            'survey_template_id' => ['nullable', 'integer', 'exists:survey_templates,id'],
            'welcome_title' => ['required', 'string', 'max:160'],
            'welcome_text' => ['nullable', 'string', 'max:2000'],
            'terms_text' => ['nullable', 'string', 'max:4000'],
            'network_vendor' => ['required', 'string', 'max:50'],
            'session_ttl_minutes' => ['required', 'integer', 'min:30', 'max:1440'],
            'post_login_redirect_url' => ['nullable', 'url', 'max:255'],
            'require_marketing_consent' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'logo_override' => ['nullable', 'image', 'max:2048'],
        ]);

        if (! empty($validated['survey_template_id'])) {
            $templateOwned = SurveyTemplate::query()
                ->where('organization_id', $organization->id)
                ->whereKey($validated['survey_template_id'])
                ->exists();

            abort_if(! $templateOwned, 422, 'Selected template does not belong to your organization.');
        }

        $portal->fill([
            'survey_template_id' => $validated['survey_template_id'] ?? null,
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'welcome_title' => $validated['welcome_title'],
            'welcome_text' => $validated['welcome_text'] ?? null,
            'terms_text' => $validated['terms_text'] ?? null,
            'network_vendor' => $validated['network_vendor'],
            'session_ttl_minutes' => $validated['session_ttl_minutes'],
            'post_login_redirect_url' => $validated['post_login_redirect_url'] ?? null,
            'require_marketing_consent' => (bool) ($validated['require_marketing_consent'] ?? false),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        if ($request->hasFile('logo_override')) {
            $portal->logo_override_path = $request->file('logo_override')->store('portal-logos', 'public');
        }

        $portal->save();

        AuditTrail::record(
            action: 'organization.portal.updated',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $portal,
            request: $request
        );

        return redirect()
            ->route('organization.portals.show', $portal)
            ->with('status', 'Portal updated successfully.');
    }

    public function destroy(Request $request, WifiPortal $portal): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($portal->organization_id !== $organization->id, 404);

        $portal->delete();

        AuditTrail::record(
            action: 'organization.portal.deleted',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $portal,
            request: $request
        );

        return redirect()
            ->route('organization.portals.index')
            ->with('status', 'Portal removed.');
    }

    public function updateTemplate(Request $request, WifiPortal $portal): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($portal->organization_id !== $organization->id, 404);

        $validated = $request->validate([
            'survey_template_id' => ['nullable', 'integer', 'exists:survey_templates,id'],
        ]);

        $templateId = $validated['survey_template_id'] ?? null;

        if ($templateId !== null) {
            $templateOwned = SurveyTemplate::query()
                ->where('organization_id', $organization->id)
                ->where('is_active', true)
                ->whereKey($templateId)
                ->exists();

            abort_if(! $templateOwned, 422, 'Selected template does not belong to your organization.');
        }

        $portal->survey_template_id = $templateId;
        $portal->save();

        AuditTrail::record(
            action: 'organization.portal.template_switched',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $portal,
            payload: ['survey_template_id' => $templateId],
            request: $request
        );

        return redirect()
            ->route('organization.portals.show', $portal)
            ->with('status', 'Portal survey template updated successfully.');
    }
}
