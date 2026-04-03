<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SurveyTemplate;
use App\Models\WifiPortal;
use App\Models\WifiSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptivePortalFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_submit_survey_and_mark_session_completed(): void
    {
        $organization = Organization::query()->create([
            'name' => 'Portal Test Org',
            'slug' => 'portal-test-org',
            'timezone' => 'Asia/Baku',
        ]);

        $template = SurveyTemplate::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Main Survey',
            'is_default' => true,
            'is_active' => true,
        ]);

        $question = $template->questions()->create([
            'question_key' => 'q1',
            'label' => 'Rate us',
            'type' => 'rating',
            'is_required' => true,
            'order_index' => 1,
        ]);

        $portal = WifiPortal::query()->create([
            'organization_id' => $organization->id,
            'survey_template_id' => $template->id,
            'name' => 'Main Portal',
            'slug' => 'main-portal',
            'portal_token' => 'token_abc_123',
            'integration_key' => 'pk_test_123',
            'integration_secret' => 'secret_123',
        ]);

        $session = WifiSession::query()->create([
            'organization_id' => $organization->id,
            'wifi_portal_id' => $portal->id,
            'status' => 'initiated',
            'expires_at' => now()->addMinutes(60),
        ]);

        $response = $this->post(route('portal.submit', $portal), [
            'session_token' => $session->session_token,
            'first_name' => 'Aysel',
            'phone' => '+994501112233',
            'gender' => 'female',
            'consent_terms' => '1',
            'consent_marketing' => '1',
            'answers' => [
                $question->id => '5',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertSee('Internet access is being enabled.');

        $session->refresh();

        $this->assertSame('survey_completed', $session->status);
        $this->assertNotNull($session->guest_id);
        $this->assertNotNull($session->survey_submitted_at);
    }
}
