<?php

namespace App\Http\Middleware;

use App\Models\WifiPortal;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureGatewaySignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $portalKey = (string) $request->header('X-Portal-Key', '');
        $timestamp = (int) $request->header('X-Timestamp', 0);
        $signature = (string) $request->header('X-Signature', '');

        if ($portalKey === '' || $timestamp <= 0 || $signature === '') {
            abort(401, 'Missing gateway authentication headers.');
        }

        if (abs(now()->timestamp - $timestamp) > 300) {
            abort(401, 'Gateway signature expired.');
        }

        $portal = WifiPortal::query()
            ->where('integration_key', $portalKey)
            ->where('is_active', true)
            ->first();

        if (! $portal) {
            abort(401, 'Unknown gateway portal key.');
        }

        $payload = implode('.', [
            (string) $timestamp,
            strtoupper($request->method()),
            '/'.$request->path(),
            $request->getContent(),
        ]);

        $expected = hash_hmac('sha256', $payload, $portal->integration_secret);

        if (! hash_equals($expected, $signature)) {
            abort(401, 'Invalid gateway signature.');
        }

        $request->attributes->set('gatewayPortal', $portal);

        return $next($request);
    }
}
