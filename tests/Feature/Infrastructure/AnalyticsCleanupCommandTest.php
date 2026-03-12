<?php

use App\Models\AnalyticsDailySummary;
use App\Models\AnalyticsEvent;
use Carbon\CarbonImmutable;

it('aggregates and cleans old analytics events', function (): void {
    CarbonImmutable::setTestNow('2026-03-11 12:00:00');

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'page_url' => '/projects/alpha',
        'device_type' => 'desktop',
        'country' => 'eg',
        'created_at' => now()->subDays(200),
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'page_url' => '/projects/alpha',
        'device_type' => 'desktop',
        'country' => 'EG',
        'created_at' => now()->subDays(200),
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'outbound_click',
        'page_url' => '/projects/alpha',
        'device_type' => 'mobile',
        'country' => 'US',
        'created_at' => now()->subDays(220),
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'page_url' => '/projects/recent',
        'device_type' => 'desktop',
        'country' => 'EG',
        'created_at' => now()->subDays(20),
    ]);

    $this->artisan('analytics:cleanup --days=180')
        ->expectsOutputToContain('Aggregated 2 summary rows and deleted 3 raw events older than 180 days.')
        ->assertSuccessful();

    expect(AnalyticsEvent::query()->count())->toBe(1)
        ->and(AnalyticsDailySummary::query()->count())->toBe(2);

    $summary = AnalyticsDailySummary::query()
        ->where('event_type', 'page_view')
        ->where('page_url', '/projects/alpha')
        ->firstOrFail();

    expect($summary->events_count)->toBe(2)
        ->and($summary->country)->toBe('EG');

    CarbonImmutable::setTestNow();
});
