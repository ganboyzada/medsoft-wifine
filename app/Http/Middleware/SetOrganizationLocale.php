<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use App\Models\WifiPortal;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetOrganizationLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = config('app.locale', Organization::LANGUAGE_AZERBAIJANI);

        if ($request->user()?->organization?->default_language) {
            $locale = $request->user()->organization->default_language;
        } else {
            $portalParam = $request->route('portal');

            if ($portalParam instanceof WifiPortal) {
                $locale = $portalParam->organization?->default_language ?? $locale;
            } elseif (is_string($portalParam) && $portalParam !== '') {
                $portalLocale = WifiPortal::query()
                    ->where('slug', $portalParam)
                    ->with('organization:id,default_language')
                    ->first()
                    ?->organization
                    ?->default_language;

                if ($portalLocale) {
                    $locale = $portalLocale;
                }
            }
        }

        if (! in_array($locale, Organization::supportedLanguages(), true)) {
            $locale = Organization::LANGUAGE_AZERBAIJANI;
        }

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }
}
