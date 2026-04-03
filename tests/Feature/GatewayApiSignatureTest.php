<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\SurveyTemplate;
use App\Models\WifiPortal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GatewayApiSignatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_gateway_can_open_session_with_valid_signature(): void
    {
        $organization = Organization::query()->create([
            'name' => 'Gateway Org',
            'slug' => 'gateway-org',
            'timezone' => 'Asia/Baku',
        ]);

        $template = SurveyTemplate::query()->create([
            'organization_id' => $organization->id,
            'name' => 'Gateway Survey',
            'is_default' => true,
            'is_active' => true,
        ]);

        $portal = WifiPortal::query()->create([
            'organization_id' => $organization->id,
            'survey_template_id' => $template->id,
            'name' => 'Gateway Portal',
            'slug' => 'gateway-portal',
            'portal_token' => 'token_gateway',
            'integration_key' => 'pk_gateway_123',
            'integration_secret' => 'gateway_secret_123',
        ]);

        $payload = [
            'client_mac' => 'AA:BB:CC:DD:EE:FF',
            'ap_mac' => '11:22:33:44:55:66',
            'ip_address' => '10.0.0.8',
        ];

        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = now()->timestamp;
        $path = '/api/gateway/sessions/open';
        $signature = hash_hmac('sha256', "{$timestamp}.POST.{$path}.{$json}", $portal->integration_secret);

        $response = $this->withHeaders([
            'X-Portal-Key' => $portal->integration_key,
            'X-Timestamp' => (string) $timestamp,
            'X-Signature' => $signature,
        ])->postJson($path, $payload);

        $response->assertCreated();
        $response->assertJsonStructure(['session_token', 'landing_url', 'status']);
    }
}
