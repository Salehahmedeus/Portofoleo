<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\seed;

test('guest hitting admin route redirects to login', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('non admin authenticated user gets forbidden response on admin route', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('non admin json request to admin route gets forbidden json response', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->getJson(route('admin.dashboard'));

    $response->assertForbidden()
        ->assertJson([
            'message' => 'Forbidden.',
        ]);
});

test('admin authenticated user can hit admin route and is redirected to dashboard', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertRedirect(route('dashboard'));
});

test('admin user seeder creates an admin user', function () {
    Config::set('admin.name', 'Seeded Admin');
    Config::set('admin.email', 'seeded-admin@example.com');
    Config::set('admin.password', 'password');

    seed(AdminUserSeeder::class);

    $admin = User::query()->where('email', 'seeded-admin@example.com')->first();

    expect($admin)->not->toBeNull()
        ->and($admin?->name)->toBe('Seeded Admin')
        ->and($admin?->is_admin)->toBeTrue()
        ->and($admin?->email_verified_at)->not->toBeNull();
});
