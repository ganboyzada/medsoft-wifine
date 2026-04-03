<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CampaignController extends Controller
{
    public function index(Request $request): View
    {
        $organization = $request->user()->organization;

        $campaigns = Campaign::query()
            ->where('organization_id', $organization->id)
            ->latest()
            ->paginate(15);

        return view('organization.campaigns.index', compact('organization', 'campaigns'));
    }

    public function create(Request $request): View
    {
        return view('organization.campaigns.create', [
            'organization' => $request->user()->organization,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:2000'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'display_rule' => ['required', 'in:all,new_guest,returning_guest'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $campaign = Campaign::query()->create([
            'organization_id' => $organization->id,
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        AuditTrail::record(
            action: 'organization.campaign.created',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $campaign,
            request: $request
        );

        return redirect()
            ->route('organization.campaigns.index')
            ->with('status', 'Campaign created.');
    }

    public function show(Campaign $campaign): RedirectResponse
    {
        return redirect()->route('organization.campaigns.edit', $campaign);
    }

    public function edit(Request $request, Campaign $campaign): View
    {
        $organization = $request->user()->organization;
        abort_if($campaign->organization_id !== $organization->id, 404);

        return view('organization.campaigns.edit', compact('organization', 'campaign'));
    }

    public function update(Request $request, Campaign $campaign): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($campaign->organization_id !== $organization->id, 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:160'],
            'body' => ['nullable', 'string', 'max:2000'],
            'cta_text' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url', 'max:255'],
            'image_url' => ['nullable', 'url', 'max:255'],
            'display_rule' => ['required', 'in:all,new_guest,returning_guest'],
            'is_active' => ['nullable', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $campaign->update([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        AuditTrail::record(
            action: 'organization.campaign.updated',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $campaign,
            request: $request
        );

        return redirect()
            ->route('organization.campaigns.index')
            ->with('status', 'Campaign updated.');
    }

    public function destroy(Request $request, Campaign $campaign): RedirectResponse
    {
        $organization = $request->user()->organization;
        abort_if($campaign->organization_id !== $organization->id, 404);

        $campaign->delete();

        AuditTrail::record(
            action: 'organization.campaign.deleted',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $campaign,
            request: $request
        );

        return redirect()
            ->route('organization.campaigns.index')
            ->with('status', 'Campaign deleted.');
    }
}
