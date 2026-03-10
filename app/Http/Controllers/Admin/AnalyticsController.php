<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
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
            'unique_sessions' => $events->pluck('session_id')->filter()->unique()->count(),
            'top_event_types' => $eventsByType,
            'daily_totals' => $dailyTotals,
        ]);
    }
}
