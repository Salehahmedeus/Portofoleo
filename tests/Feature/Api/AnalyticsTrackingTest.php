<?php

use App\Models\AnalyticsEvent;

it('tracks page views for html get requests with metadata', function (): void {
    $this->withHeaders([
        'referer' => 'https://google.com/search?q=portfolio',
        'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        'CF-IPCountry' => 'eg',
    ])->get(route('home'))->assertSuccessful();

    $event = AnalyticsEvent::query()->firstOrFail();

    expect($event->event_type)->toBe('page_view')
        ->and($event->page_url)->toContain('/')
        ->and($event->referrer)->toBe('https://google.com/search?q=portfolio')
        ->and($event->device_type)->toBe('mobile')
        ->and($event->country)->toBe('EG')
        ->and($event->event_data)->toMatchArray([
            'route_name' => 'home',
        ]);
});

it('does not track json requests or auth routes', function (): void {
    $this->getJson(route('home'))->assertSuccessful();
    $this->get('/login')->assertSuccessful();

    expect(AnalyticsEvent::query()->count())->toBe(0);
});

it('stores custom analytics events from api endpoint', function (): void {
    $response = $this->postJson(route('api.analytics-events.track'), [
        'event_type' => 'outbound_click',
        'event_data' => ['target' => 'github'],
        'page_url' => 'https://portofolio.test/projects/alpha',
        'referrer' => 'https://linkedin.com/company/example',
        'device_type' => 'desktop',
        'country' => 'us',
    ]);

    $response->assertCreated()
        ->assertJsonPath('message', 'Event stored successfully.');

    $event = AnalyticsEvent::query()->where('event_type', 'outbound_click')->firstOrFail();

    expect($event->country)->toBe('US')
        ->and($event->event_data)->toMatchArray(['target' => 'github']);
});
