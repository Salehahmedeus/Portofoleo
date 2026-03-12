<?php

namespace App\Http\Middleware;

use App\Models\AnalyticsEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalyticsPageView
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldTrack($request)) {
            AnalyticsEvent::query()->create([
                'event_type' => 'page_view',
                'event_data' => [
                    'route_name' => $request->route()?->getName(),
                ],
                'page_url' => $request->fullUrl(),
                'referrer' => $request->headers->get('referer'),
                'device_type' => $this->resolveDeviceType($request),
                'country' => $this->resolveCountry($request),
                'ip_address' => $request->ip(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            ]);
        }

        return $next($request);
    }

    private function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return false;
        }

        if (! $request->acceptsHtml() || $request->expectsJson() || $request->wantsJson()) {
            return false;
        }

        return ! $request->is([
            'admin',
            'admin/*',
            'api',
            'api/*',
            'login',
            'register',
            'forgot-password',
            'reset-password',
            'reset-password/*',
            'confirm-password',
            'password',
            'password/*',
            'user/password',
        ]);
    }

    private function resolveDeviceType(Request $request): string
    {
        $userAgent = strtolower((string) $request->userAgent());

        if ($userAgent === '') {
            return 'desktop';
        }

        if (str_contains($userAgent, 'ipad') || str_contains($userAgent, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android') || str_contains($userAgent, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function resolveCountry(Request $request): ?string
    {
        $country = $request->header('CF-IPCountry')
            ?? $request->header('X-Country-Code')
            ?? $request->header('X-AppEngine-Country');

        if (! is_string($country) || strlen($country) !== 2) {
            return null;
        }

        return strtoupper($country);
    }
}
