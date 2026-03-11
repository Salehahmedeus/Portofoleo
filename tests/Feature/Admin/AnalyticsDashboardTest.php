<?php

use App\Models\AnalyticsEvent;
use App\Models\ContactSubmission;
use App\Models\Project;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can view analytics page and expected inertia props exist', function () {
    $admin = User::factory()->admin()->create();
    $project = Project::factory()->create([
        'slug' => 'alpha-project',
        'title' => 'Alpha Project',
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'page_view',
        'page_url' => '/',
        'referrer' => 'https://google.com',
        'device_type' => 'desktop',
        'country' => 'EG',
        'session_id' => 'sess-1',
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'project_view',
        'event_data' => ['project_slug' => $project->slug],
        'page_url' => '/projects/'.$project->slug,
        'referrer' => 'https://linkedin.com',
        'device_type' => 'mobile',
        'country' => 'US',
        'session_id' => 'sess-2',
    ]);

    AnalyticsEvent::factory()->create([
        'event_type' => 'outbound_click',
        'event_data' => ['target' => 'github'],
        'page_url' => '/projects/'.$project->slug,
        'referrer' => null,
        'device_type' => null,
        'country' => null,
        'session_id' => 'sess-2',
    ]);

    ContactSubmission::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.analytics.index', ['range' => 30]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page->component('admin/analytics/index'));

    $props = $response->inertiaProps();

    expect($props)->toHaveKeys([
        'range',
        'available_ranges',
        'total_events',
        'unique_sessions',
        'top_event_types',
        'daily_totals',
        'total_visitors',
        'page_views',
        'contact_submissions_count',
        'top_project',
        'top_project_views',
        'traffic_sources',
        'device_types',
        'country_distribution',
        'contact_submissions',
        'outbound_clicks',
    ]);
});

test('analytics range defaults to 30 for invalid values', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.analytics.index', ['range' => 999]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('admin/analytics/index')
        ->where('range', 30),
    );
});
