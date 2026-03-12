<?php

use App\Models\AnalyticsEvent;
use App\Models\ContactSubmission;
use App\Models\Project;
use App\Models\User;
use Carbon\CarbonImmutable;

beforeEach(function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
    CarbonImmutable::setTestNow('2026-03-11 12:00:00');
});

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('returns overview metrics for a preset range', function (): void {
    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'session_id' => 'session-1',
        'ip_address' => '10.0.0.1',
        'created_at' => now()->subDay(),
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'session_id' => 'session-2',
        'ip_address' => '10.0.0.2',
        'created_at' => now(),
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'outbound_click',
        'session_id' => 'session-2',
        'ip_address' => '10.0.0.2',
        'created_at' => now(),
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'session_id' => 'session-old',
        'ip_address' => '10.0.0.3',
        'created_at' => now()->subDays(12),
    ]);

    ContactSubmission::factory()->create([
        'created_at' => now()->subDay(),
    ]);

    ContactSubmission::factory()->create([
        'created_at' => now()->subDays(15),
    ]);

    $this->getJson(route('admin.analytics.overview', ['range' => '7d']))
        ->assertSuccessful()
        ->assertJsonPath('range', '7d')
        ->assertJsonPath('total_visitors', 2)
        ->assertJsonPath('page_views', 2)
        ->assertJsonPath('unique_visitors', 2)
        ->assertJsonPath('contact_submissions_count', 1);
});

it('returns analytics breakdown endpoints for custom range', function (): void {
    $project = Project::factory()->create([
        'slug' => 'alpha-project',
        'title' => 'Alpha Project',
    ]);

    $inRangeTimestamp = now()->subDays(2);

    AnalyticsEvent::factory()->create([
        'event_type' => 'project_view',
        'event_data' => ['project_slug' => $project->slug],
        'page_url' => '/projects/'.$project->slug,
        'referrer' => 'https://google.com/search?q=alpha',
        'device_type' => 'desktop',
        'country' => 'EG',
        'created_at' => $inRangeTimestamp,
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'outbound_click',
        'event_data' => ['target' => 'github'],
        'page_url' => '/projects/'.$project->slug,
        'referrer' => 'https://linkedin.com/company/example',
        'device_type' => 'mobile',
        'country' => 'US',
        'created_at' => $inRangeTimestamp,
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'page_url' => '/projects/'.$project->slug,
        'referrer' => null,
        'device_type' => 'tablet',
        'country' => null,
        'created_at' => now()->subDays(20),
    ]);

    $query = [
        'range' => 'custom',
        'start_date' => now()->subDays(7)->toDateString(),
        'end_date' => now()->toDateString(),
    ];

    $this->getJson(route('admin.analytics.top-projects', $query))
        ->assertSuccessful()
        ->assertJsonPath('range', 'custom')
        ->assertJsonPath('data.0.slug', 'alpha-project')
        ->assertJsonPath('data.0.views', 1);

    $this->getJson(route('admin.analytics.sources', $query))
        ->assertSuccessful()
        ->assertJsonPath('data.search', 1)
        ->assertJsonPath('data.social', 1);

    $this->getJson(route('admin.analytics.devices', $query))
        ->assertSuccessful()
        ->assertJsonPath('data.desktop', 1)
        ->assertJsonPath('data.mobile', 1)
        ->assertJsonPath('data.tablet', 0);

    $this->getJson(route('admin.analytics.countries', $query))
        ->assertSuccessful()
        ->assertJsonPath('data.0.country', 'EG');

    $this->getJson(route('admin.analytics.clicks', $query))
        ->assertSuccessful()
        ->assertJsonPath('data.0.target', 'github')
        ->assertJsonPath('data.0.count', 1);
});

it('requires start and end date for custom range', function (): void {
    $this->getJson(route('admin.analytics.overview', ['range' => 'custom']))
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['start_date', 'end_date']);

});
