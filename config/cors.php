<?php

$defaultAllowedOrigins = env('APP_URL')
    ? env('APP_URL').',http://localhost'
    : 'http://localhost';

$parseCsv = static function (?string $value, array $default = []): array {
    if ($value === null || trim($value) === '') {
        return $default;
    }

    return array_values(array_filter(array_map(
        static fn (string $item): string => trim($item),
        explode(',', $value)
    ), static fn (string $item): bool => $item !== ''));
};

return [
    'paths' => $parseCsv(env('CORS_PATHS', 'api/*,sanctum/csrf-cookie')),

    'allowed_methods' => $parseCsv(env('CORS_ALLOWED_METHODS', '*')),

    'allowed_origins' => $parseCsv(env('CORS_ALLOWED_ORIGINS', $defaultAllowedOrigins), ['http://localhost']),

    'allowed_origins_patterns' => $parseCsv(env('CORS_ALLOWED_ORIGIN_PATTERNS', '')),

    'allowed_headers' => $parseCsv(env('CORS_ALLOWED_HEADERS', '*')),

    'exposed_headers' => $parseCsv(env('CORS_EXPOSED_HEADERS', '')),

    'max_age' => (int) env('CORS_MAX_AGE', 0),

    'supports_credentials' => env('CORS_SUPPORTS_CREDENTIALS', false),
];
