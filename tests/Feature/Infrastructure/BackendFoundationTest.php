<?php

test('home route is reachable', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
});

test('api health endpoint returns ok payload with timestamp', function () {
    $response = $this->getJson(route('api.health'));

    $response->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonStructure(['status', 'timestamp']);
});

test('unknown api endpoint returns not found json message', function () {
    $response = $this->getJson('/api/this-endpoint-does-not-exist');

    $response->assertNotFound()
        ->assertJson([
            'message' => 'Resource not found.',
        ]);
});

test('admin route redirects guests to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('cors preflight to api health includes allow origin header', function () {
    $response = $this->withHeaders([
        'Origin' => 'http://localhost',
        'Access-Control-Request-Method' => 'GET',
    ])->options('/api/health');

    $response->assertNoContent()
        ->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
});
