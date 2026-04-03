<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WifiPortal;
use App\Models\WifiSession;
use App\Support\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GatewaySessionController extends Controller
{
    public function open(Request $request): JsonResponse
    {
        /** @var WifiPortal $portal */
        $portal = $request->attributes->get('gatewayPortal');

        $validated = $request->validate([
            'client_mac' => ['nullable', 'string', 'max:64'],
            'ap_mac' => ['nullable', 'string', 'max:64'],
            'ip_address' => ['nullable', 'ip'],
            'redirect_url' => ['nullable', 'url', 'max:255'],
            'metadata' => ['nullable', 'array'],
        ]);

        $session = WifiSession::query()->create([
            'organization_id' => $portal->organization_id,
            'wifi_portal_id' => $portal->id,
            'status' => 'initiated',
            'client_mac' => $validated['client_mac'] ?? null,
            'ap_mac' => $validated['ap_mac'] ?? null,
            'ip_address' => $validated['ip_address'] ?? $request->ip(),
            'user_agent' => $request->userAgent(),
            'redirect_url' => $validated['redirect_url'] ?? null,
            'metadata' => $validated['metadata'] ?? [],
            'expires_at' => now()->addMinutes($portal->session_ttl_minutes),
        ]);

        $landingUrl = route('portal.show', [
            'portal' => $portal->slug,
            'session_token' => $session->session_token,
            'client_mac' => $session->client_mac,
            'ap_mac' => $session->ap_mac,
        ]);

        return response()->json([
            'session_token' => $session->session_token,
            'status' => $session->status,
            'landing_url' => $landingUrl,
            'expires_at' => optional($session->expires_at)->toIso8601String(),
        ], 201);
    }

    public function status(Request $request, string $sessionToken): JsonResponse
    {
        /** @var WifiPortal $portal */
        $portal = $request->attributes->get('gatewayPortal');

        $session = WifiSession::query()
            ->with('guest')
            ->where('wifi_portal_id', $portal->id)
            ->where('session_token', $sessionToken)
            ->firstOrFail();

        if ($session->expires_at && now()->gt($session->expires_at) && $session->status !== 'authorized') {
            $session->update(['status' => 'expired']);
        }

        return response()->json([
            'session_token' => $session->session_token,
            'status' => $session->status,
            'grant_wifi' => in_array($session->status, ['survey_completed', 'authorized'], true),
            'guest' => $session->guest ? [
                'first_name' => $session->guest->first_name,
                'phone' => $session->guest->phone_normalized,
                'gender' => $session->guest->gender,
                'consent_marketing' => $session->guest->consent_marketing,
            ] : null,
            'redirect_url' => $session->redirect_url ?: $portal->post_login_redirect_url,
            'expires_at' => optional($session->expires_at)->toIso8601String(),
        ]);
    }

    public function authorizeAccess(Request $request, string $sessionToken): JsonResponse
    {
        /** @var WifiPortal $portal */
        $portal = $request->attributes->get('gatewayPortal');

        $session = WifiSession::query()
            ->where('wifi_portal_id', $portal->id)
            ->where('session_token', $sessionToken)
            ->firstOrFail();

        if (! in_array($session->status, ['survey_completed', 'authorized'], true)) {
            return response()->json([
                'message' => 'Survey not completed yet.',
                'status' => $session->status,
            ], 422);
        }

        $session->markAuthorized();
        $session->save();

        AuditTrail::record(
            action: 'gateway.session.authorized',
            organizationId: $portal->organization_id,
            subject: $session,
            payload: ['portal_id' => $portal->id]
        );

        return response()->json([
            'message' => 'Session authorized.',
            'status' => $session->status,
            'authorized_at' => optional($session->authorized_at)->toIso8601String(),
        ]);
    }
}
