<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\ContactSubmission;
use App\Models\Project;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        $allowedRanges = [7, 30, 90];
        $range = (int) $request->integer('range', 30);

        if (! in_array($range, $allowedRanges, true)) {
            $range = 30;
        }

        $startDate = CarbonImmutable::today()->subDays($range - 1)->startOfDay();
        $endDate = CarbonImmutable::today()->endOfDay();

        $events = AnalyticsEvent::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();

        $eventsByType = $events
            ->groupBy('event_type')
            ->map(fn ($group): int => $group->count())
            ->sortKeys()
            ->all();

        $uniqueSessions = $events->pluck('session_id')->filter()->unique()->count();

        $topProjectViews = $this->topProjectViews($events);

        $contactSubmissionsQuery = ContactSubmission::query()
            ->whereBetween('created_at', [$startDate, $endDate]);

        $contactSubmissions = (clone $contactSubmissionsQuery)
            ->latest()
            ->get(['id', 'name', 'email', 'subject', 'message', 'created_at', 'read'])
            ->map(function (ContactSubmission $submission): array {
                return [
                    'id' => $submission->id,
                    'name' => $submission->name,
                    'email' => $submission->email,
                    'subject' => $submission->subject,
                    'message' => $submission->message,
                    'created_at' => $submission->created_at?->toISOString(),
                    'read' => $submission->read,
                ];
            })
            ->all();

        $timeline = $events
            ->groupBy(fn (AnalyticsEvent $event): string => $event->created_at->toDateString())
            ->map(fn ($group): int => $group->count())
            ->all();

        $dailyTotals = collect(range(0, $range - 1))
            ->map(function (int $offset) use ($startDate, $timeline): array {
                $date = $startDate->addDays($offset)->toDateString();

                return [
                    'date' => $date,
                    'count' => $timeline[$date] ?? 0,
                ];
            })
            ->all();

        return Inertia::render('admin/analytics/index', [
            'range' => $range,
            'available_ranges' => $allowedRanges,
            'total_events' => $events->count(),
            'unique_sessions' => $uniqueSessions,
            'top_event_types' => $eventsByType,
            'daily_totals' => $dailyTotals,
            'total_visitors' => $uniqueSessions,
            'page_views' => $events->where('event_type', 'page_view')->count(),
            'contact_submissions_count' => $contactSubmissionsQuery->count(),
            'top_project' => $topProjectViews[0] ?? null,
            'top_project_views' => $topProjectViews,
            'traffic_sources' => $this->trafficSources($events),
            'device_types' => $this->deviceTypes($events),
            'country_distribution' => $this->countryDistribution($events),
            'contact_submissions' => $contactSubmissions,
            'outbound_clicks' => $this->outboundClicks($events),
        ]);
    }

    public function overview(Request $request): JsonResponse
    {
        [$startDate, $endDate, $range] = $this->resolveDateRange($request);
        $events = $this->eventsWithin($startDate, $endDate);

        $totalVisitors = $events
            ->map(fn (AnalyticsEvent $event): ?string => $event->session_id ?? $event->ip_address)
            ->filter()
            ->unique()
            ->count();

        $uniqueVisitors = $events
            ->where('event_type', 'page_view')
            ->map(fn (AnalyticsEvent $event): ?string => $event->session_id ?? $event->ip_address)
            ->filter()
            ->unique()
            ->count();

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_visitors' => $totalVisitors,
            'page_views' => $events->where('event_type', 'page_view')->count(),
            'unique_visitors' => $uniqueVisitors,
            'contact_submissions_count' => ContactSubmission::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ]);
    }

    public function topProjects(Request $request): JsonResponse
    {
        [$startDate, $endDate, $range] = $this->resolveDateRange($request);

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $this->topProjectViews($this->eventsWithin($startDate, $endDate)),
        ]);
    }

    public function sources(Request $request): JsonResponse
    {
        [$startDate, $endDate, $range] = $this->resolveDateRange($request);

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $this->trafficSources($this->eventsWithin($startDate, $endDate)),
        ]);
    }

    public function devices(Request $request): JsonResponse
    {
        [$startDate, $endDate, $range] = $this->resolveDateRange($request);

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $this->deviceTypes($this->eventsWithin($startDate, $endDate)),
        ]);
    }

    public function countries(Request $request): JsonResponse
    {
        [$startDate, $endDate, $range] = $this->resolveDateRange($request);

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $this->countryDistribution($this->eventsWithin($startDate, $endDate)),
        ]);
    }

    public function clicks(Request $request): JsonResponse
    {
        [$startDate, $endDate, $range] = $this->resolveDateRange($request);

        return response()->json([
            'range' => $range,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'data' => $this->outboundClicks($this->eventsWithin($startDate, $endDate)),
        ]);
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable, 2: string}
     */
    private function resolveDateRange(Request $request): array
    {
        $range = (string) $request->query('range', '30d');
        $presetRanges = [
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
        ];

        if (array_key_exists($range, $presetRanges)) {
            $days = $presetRanges[$range];

            return [
                CarbonImmutable::today()->subDays($days - 1)->startOfDay(),
                CarbonImmutable::today()->endOfDay(),
                $range,
            ];
        }

        if ($range !== 'custom') {
            return [
                CarbonImmutable::today()->subDays(29)->startOfDay(),
                CarbonImmutable::today()->endOfDay(),
                '30d',
            ];
        }

        $validated = Validator::make($request->query(), [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ])->validate();

        $startDate = CarbonImmutable::parse($validated['start_date'])->startOfDay();
        $endDate = CarbonImmutable::parse($validated['end_date'])->endOfDay();

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'The end date must be after or equal to the start date.',
            ]);
        }

        return [$startDate, $endDate, 'custom'];
    }

    /**
     * @return Collection<int, AnalyticsEvent>
     */
    private function eventsWithin(CarbonImmutable $startDate, CarbonImmutable $endDate): Collection
    {
        return AnalyticsEvent::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * @param  Collection<int, AnalyticsEvent>  $events
     * @return list<array{slug: string, title: string, views: int}>
     */
    private function topProjectViews(Collection $events): array
    {
        $slugCounts = $events
            ->filter(fn (AnalyticsEvent $event): bool => in_array($event->event_type, ['page_view', 'project_view'], true))
            ->map(fn (AnalyticsEvent $event): ?string => $this->extractProjectSlug($event))
            ->filter()
            ->countBy()
            ->sortDesc();

        if ($slugCounts->isEmpty()) {
            return [];
        }

        $titlesBySlug = Project::query()
            ->whereIn('slug', $slugCounts->keys()->all())
            ->get(['slug', 'title'])
            ->mapWithKeys(fn (Project $project): array => [$project->slug => $project->title]);

        return $slugCounts
            ->map(function (int $views, string $slug) use ($titlesBySlug): array {
                return [
                    'slug' => $slug,
                    'title' => $titlesBySlug->get($slug, $slug),
                    'views' => $views,
                ];
            })
            ->values()
            ->all();
    }

    private function extractProjectSlug(AnalyticsEvent $event): ?string
    {
        $projectSlug = $event->event_data['project_slug'] ?? null;

        if (is_string($projectSlug) && $projectSlug !== '') {
            return $projectSlug;
        }

        $path = parse_url($event->page_url, PHP_URL_PATH);

        if (! is_string($path)) {
            return null;
        }

        if (! preg_match('#^/projects/([^/]+)$#', $path, $matches)) {
            return null;
        }

        return $matches[1] !== '' ? $matches[1] : null;
    }

    /**
     * @param  Collection<int, AnalyticsEvent>  $events
     * @return array{direct: int, social: int, search: int, referral: int}
     */
    private function trafficSources(Collection $events): array
    {
        $summary = [
            'direct' => 0,
            'social' => 0,
            'search' => 0,
            'referral' => 0,
        ];

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        $events->each(function (AnalyticsEvent $event) use (&$summary, $appHost): void {
            $source = $this->resolveTrafficSource($event->referrer, is_string($appHost) ? $appHost : null);
            $summary[$source]++;
        });

        return $summary;
    }

    private function resolveTrafficSource(?string $referrer, ?string $appHost): string
    {
        if ($referrer === null || trim($referrer) === '') {
            return 'direct';
        }

        $referrerHost = parse_url($referrer, PHP_URL_HOST);

        if (! is_string($referrerHost) || $referrerHost === '') {
            return 'referral';
        }

        if ($appHost !== null && str_ends_with($referrerHost, $appHost)) {
            return 'direct';
        }

        $socialHosts = [
            'facebook.com',
            'instagram.com',
            'linkedin.com',
            'twitter.com',
            'x.com',
            't.co',
            'youtube.com',
            'pinterest.com',
            'tiktok.com',
        ];

        foreach ($socialHosts as $host) {
            if (str_ends_with($referrerHost, $host)) {
                return 'social';
            }
        }

        $searchHosts = [
            'google.com',
            'bing.com',
            'duckduckgo.com',
            'yahoo.com',
            'baidu.com',
            'yandex.com',
        ];

        foreach ($searchHosts as $host) {
            if (str_ends_with($referrerHost, $host)) {
                return 'search';
            }
        }

        return 'referral';
    }

    /**
     * @param  Collection<int, AnalyticsEvent>  $events
     * @return array{desktop: int, mobile: int, tablet: int, unknown: int}
     */
    private function deviceTypes(Collection $events): array
    {
        $summary = [
            'desktop' => 0,
            'mobile' => 0,
            'tablet' => 0,
            'unknown' => 0,
        ];

        $events->each(function (AnalyticsEvent $event) use (&$summary): void {
            $deviceType = $event->device_type;

            if (! is_string($deviceType) || ! array_key_exists($deviceType, $summary)) {
                $summary['unknown']++;

                return;
            }

            $summary[$deviceType]++;
        });

        return $summary;
    }

    /**
     * @param  Collection<int, AnalyticsEvent>  $events
     * @return list<array{country: string, count: int}>
     */
    private function countryDistribution(Collection $events): array
    {
        return $events
            ->map(function (AnalyticsEvent $event): string {
                if (! is_string($event->country) || $event->country === '') {
                    return 'UNKNOWN';
                }

                return strtoupper($event->country);
            })
            ->countBy()
            ->sortDesc()
            ->map(fn (int $count, string $country): array => [
                'country' => $country,
                'count' => $count,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, AnalyticsEvent>  $events
     * @return list<array{target: string, count: int}>
     */
    private function outboundClicks(Collection $events): array
    {
        return $events
            ->where('event_type', 'outbound_click')
            ->map(function (AnalyticsEvent $event): string {
                $target = $event->event_data['target'] ?? null;

                return is_string($target) && $target !== '' ? $target : 'unknown';
            })
            ->countBy()
            ->sortDesc()
            ->map(fn (int $count, string $target): array => [
                'target' => $target,
                'count' => $count,
            ])
            ->values()
            ->all();
    }
}
