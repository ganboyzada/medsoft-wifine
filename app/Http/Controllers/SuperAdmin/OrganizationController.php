<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\SurveyTemplate;
use App\Models\User;
use App\Models\WifiPortal;
use App\Support\AuditTrail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::query()
            ->withCount(['users', 'portals', 'guests'])
            ->latest()
            ->paginate(15);

        return view('superadmin.organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('superadmin.organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'alpha_dash', 'max:150', 'unique:organizations,slug'],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['required', 'string', 'max:80'],
            'default_language' => ['required', 'in:az,en'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'admin_name' => ['required', 'string', 'max:120'],
            'admin_email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'admin_password' => ['required', 'string', 'min:10'],
        ]);

        $organization = null;
        $portal = null;

        DB::transaction(function () use (&$organization, &$portal, $validated, $request): void {
            $organization = Organization::query()->create([
                'name' => $validated['name'],
                'slug' => $validated['slug'] ?? Str::slug($validated['name'].'-'.Str::random(4)),
                'legal_name' => $validated['legal_name'] ?? null,
                'contact_email' => $validated['contact_email'] ?? null,
                'contact_phone' => $validated['contact_phone'] ?? null,
                'timezone' => $validated['timezone'],
                'default_language' => $validated['default_language'],
                'primary_color' => $validated['primary_color'] ?? '#0F766E',
                'accent_color' => $validated['accent_color'] ?? '#0369A1',
            ]);

            if ($request->hasFile('logo')) {
                $organization->update([
                    'logo_path' => $request->file('logo')->store('logos', 'public'),
                ]);
            }

            $isAzerbaijani = $organization->default_language === 'az';

            $template = SurveyTemplate::query()->create([
                'organization_id' => $organization->id,
                'name' => $isAzerbaijani ? 'Standart Məmnuniyyət Sorğusu' : 'Default Satisfaction Survey',
                'description' => $isAzerbaijani
                    ? 'İlk quraşdırma üçün avtomatik yaradılıb.'
                    : 'Generated automatically for first-time onboarding.',
                'is_default' => true,
                'is_active' => true,
            ]);

            $template->questions()->createMany([
                [
                    'question_key' => 'q_rating',
                    'label' => $isAzerbaijani
                        ? 'Bugünkü ümumi təcrübənizi necə qiymətləndirərdiniz?'
                        : 'How would you rate your overall experience today?',
                    'type' => 'rating',
                    'is_required' => true,
                    'order_index' => 1,
                    'settings' => ['min' => 1, 'max' => 5],
                ],
                [
                    'question_key' => 'q_nps',
                    'label' => $isAzerbaijani
                        ? 'Bizi dostunuza tövsiyə etmə ehtimalınız nə qədərdir?'
                        : 'How likely are you to recommend us to a friend?',
                    'type' => 'nps',
                    'is_required' => true,
                    'order_index' => 2,
                ],
                [
                    'question_key' => 'q_topic',
                    'label' => $isAzerbaijani ? 'Nəyi yaxşılaşdıra bilərik?' : 'What can we improve?',
                    'type' => 'single_choice',
                    'is_required' => true,
                    'order_index' => 3,
                    'options' => $isAzerbaijani
                        ? ['Xidmət sürəti', 'Personal dəstəyi', 'WiFi keyfiyyəti', 'Məhsul keyfiyyəti']
                        : ['Service speed', 'Staff support', 'WiFi quality', 'Food quality'],
                ],
                [
                    'question_key' => 'q_comment',
                    'label' => $isAzerbaijani ? 'Əlavə rəyiniz varmı?' : 'Any additional comments?',
                    'type' => 'long_text',
                    'is_required' => false,
                    'order_index' => 4,
                ],
            ]);

            $portal = WifiPortal::query()->create([
                'organization_id' => $organization->id,
                'survey_template_id' => $template->id,
                'name' => $organization->name.($isAzerbaijani ? ' Əsas Qonaq WiFi' : ' Main Guest WiFi'),
                'slug' => Str::slug($organization->name.'-guest-'.Str::lower(Str::random(4))),
                'welcome_title' => $isAzerbaijani
                    ? $organization->name.' WiFi şəbəkəsinə xoş gəlmisiniz'
                    : 'Welcome to '.$organization->name.' WiFi',
                'welcome_text' => $isAzerbaijani
                    ? 'İnternetə davam etmək üçün bu qısa formu tamamlayın.'
                    : 'Please complete this short form to continue to the internet.',
                'terms_text' => $isAzerbaijani
                    ? 'Davam etməklə istifadə qaydaları və məxfilik siyasəti ilə razılaşırsınız.'
                    : 'By continuing you agree to our acceptable use and privacy policy.',
                'network_vendor' => 'custom',
                'settings' => [
                    'allow_returning_guest_fast_track' => true,
                    'capture_device_context' => true,
                ],
            ]);

            User::query()->create([
                'organization_id' => $organization->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'role' => User::ROLE_ORG_ADMIN,
                'password' => Hash::make($validated['admin_password']),
                'is_active' => true,
            ]);
        });

        AuditTrail::record(
            action: 'superadmin.organization.created',
            actor: $request->user(),
            organizationId: $organization?->id,
            subject: $organization,
            payload: ['portal_id' => $portal?->id],
            request: $request
        );

        return redirect()
            ->route('superadmin.organizations.show', $organization)
            ->with('status', 'Organization provisioned successfully. Share the portal key and secret with network team.')
            ->with('integration_credentials', [
                'portal_slug' => $portal?->slug,
                'portal_key' => $portal?->integration_key,
                'portal_secret' => $portal?->integration_secret,
            ]);
    }

    public function show(Organization $organization): View
    {
        $organization->load(['users', 'portals.surveyTemplate', 'surveyTemplates.questions']);

        return view('superadmin.organizations.show', compact('organization'));
    }

    public function edit(Organization $organization): View
    {
        return view('superadmin.organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'alpha_dash', 'max:150', 'unique:organizations,slug,'.$organization->id],
            'legal_name' => ['nullable', 'string', 'max:150'],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'timezone' => ['required', 'string', 'max:80'],
            'default_language' => ['required', 'in:az,en'],
            'primary_color' => ['nullable', 'string', 'max:20'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:active,suspended'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $organization->fill($validated);

        if ($request->hasFile('logo')) {
            $organization->logo_path = $request->file('logo')->store('logos', 'public');
        }

        $organization->save();

        AuditTrail::record(
            action: 'superadmin.organization.updated',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $organization,
            request: $request
        );

        return redirect()
            ->route('superadmin.organizations.show', $organization)
            ->with('status', 'Organization updated successfully.');
    }

    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        $organization->delete();

        AuditTrail::record(
            action: 'superadmin.organization.deleted',
            actor: $request->user(),
            organizationId: $organization->id,
            subject: $organization,
            request: $request
        );

        return redirect()
            ->route('superadmin.organizations.index')
            ->with('status', 'Organization was archived.');
    }
}
