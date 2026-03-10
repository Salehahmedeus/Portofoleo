<?php

use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Features;

test('session configuration uses secure authentication defaults', function () {
    $sessionDriver = $_ENV['SESSION_DRIVER'] ?? null;
    $sessionHttpOnly = $_ENV['SESSION_HTTP_ONLY'] ?? null;
    $sessionSameSite = $_ENV['SESSION_SAME_SITE'] ?? null;

    putenv('SESSION_DRIVER');
    putenv('SESSION_HTTP_ONLY');
    putenv('SESSION_SAME_SITE');

    unset($_ENV['SESSION_DRIVER'], $_SERVER['SESSION_DRIVER']);
    unset($_ENV['SESSION_HTTP_ONLY'], $_SERVER['SESSION_HTTP_ONLY']);
    unset($_ENV['SESSION_SAME_SITE'], $_SERVER['SESSION_SAME_SITE']);

    $sessionConfig = require config_path('session.php');

    if ($sessionDriver !== null) {
        putenv("SESSION_DRIVER={$sessionDriver}");
        $_ENV['SESSION_DRIVER'] = $sessionDriver;
        $_SERVER['SESSION_DRIVER'] = $sessionDriver;
    }

    if ($sessionHttpOnly !== null) {
        putenv("SESSION_HTTP_ONLY={$sessionHttpOnly}");
        $_ENV['SESSION_HTTP_ONLY'] = $sessionHttpOnly;
        $_SERVER['SESSION_HTTP_ONLY'] = $sessionHttpOnly;
    }

    if ($sessionSameSite !== null) {
        putenv("SESSION_SAME_SITE={$sessionSameSite}");
        $_ENV['SESSION_SAME_SITE'] = $sessionSameSite;
        $_SERVER['SESSION_SAME_SITE'] = $sessionSameSite;
    }

    expect($sessionConfig['driver'])->toBe('database')
        ->and($sessionConfig['http_only'])->toBeTrue()
        ->and($sessionConfig['same_site'])->toBe('lax');
});

test('fortify login rate limiter is configured and registered', function () {
    expect(config('fortify.limiters.login'))->toBe('login')
        ->and(RateLimiter::limiter('login'))->not->toBeNull();
});

test('fortify two factor authentication feature is enabled', function () {
    expect(Features::canManageTwoFactorAuthentication())->toBeTrue();
});
