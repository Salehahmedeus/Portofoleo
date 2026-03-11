<?php

use App\Models\Service;
use App\Models\Skill;
use App\Models\User;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

test('admin can update services via admin content endpoint', function () {
    $admin = User::factory()->admin()->create();
    $existingService = Service::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.content.update'), [
        'key' => 'services',
        'group' => 'homepage',
        'value' => [
            'items' => [
                [
                    'title' => 'Backend Development',
                    'description' => 'Laravel applications and APIs.',
                    'icon' => 'server',
                ],
                [
                    'title' => 'UI Engineering',
                    'description' => 'Inertia + React interfaces.',
                    'icon' => 'layout',
                    'sort_order' => 5,
                ],
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.content.edit'));
    $response->assertSessionHas('success', 'Services updated successfully.');

    expect(Service::query()->count())->toBe(2);

    assertDatabaseMissing('services', ['id' => $existingService->id]);
    assertDatabaseHas('services', [
        'title' => 'Backend Development',
        'description' => 'Laravel applications and APIs.',
        'icon' => 'server',
        'sort_order' => 0,
    ]);
    assertDatabaseHas('services', [
        'title' => 'UI Engineering',
        'description' => 'Inertia + React interfaces.',
        'icon' => 'layout',
        'sort_order' => 5,
    ]);
});

test('admin can update skills via admin content endpoint', function () {
    $admin = User::factory()->admin()->create();
    $existingSkill = Skill::factory()->create();

    $response = $this->actingAs($admin)->put(route('admin.content.update'), [
        'key' => 'skills',
        'group' => 'homepage',
        'value' => [
            'items' => [
                [
                    'name' => 'Laravel',
                    'category' => 'backend',
                    'logo_path' => 'skills/laravel.svg',
                    'sort_order' => 0,
                ],
                [
                    'name' => 'React',
                    'category' => 'frontend',
                    'sort_order' => 8,
                ],
            ],
        ],
    ]);

    $response->assertRedirect(route('admin.content.edit'));
    $response->assertSessionHas('success', 'Skills updated successfully.');

    expect(Skill::query()->count())->toBe(2);

    assertDatabaseMissing('skills', ['id' => $existingSkill->id]);
    assertDatabaseHas('skills', [
        'name' => 'Laravel',
        'category' => 'backend',
        'logo_path' => 'skills/laravel.svg',
        'sort_order' => 0,
    ]);
    assertDatabaseHas('skills', [
        'name' => 'React',
        'category' => 'frontend',
        'sort_order' => 8,
    ]);
});

test('non admin is forbidden from updating admin content', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->put(route('admin.content.update'), [
        'key' => 'hero_content',
        'group' => 'homepage',
        'value' => [
            'headline' => 'Updated headline',
        ],
    ]);

    $response->assertForbidden();
});
