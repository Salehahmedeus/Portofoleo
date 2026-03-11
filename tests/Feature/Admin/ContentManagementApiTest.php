<?php

use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Skill;
use App\Models\User;

beforeEach(function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
});

test('settings crud works', function (): void {
    $storeResponse = $this->postJson(route('admin.settings.store'), [
        'key' => 'contact_information',
        'group' => 'contact',
        'value' => [
            'email' => 'hello@example.com',
            'phone' => '+20123456789',
        ],
    ]);

    $storeResponse->assertCreated()
        ->assertJsonPath('data.key', 'contact_information')
        ->assertJsonPath('data.group', 'contact');

    $settingId = $storeResponse->json('data.id');

    $this->getJson(route('admin.settings.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $settingId);

    $this->getJson(route('admin.settings.show', $settingId))
        ->assertSuccessful()
        ->assertJsonPath('data.key', 'contact_information');

    $updateResponse = $this->putJson(route('admin.settings.update', $settingId), [
        'key' => 'contact_information',
        'group' => 'contact',
        'value' => [
            'email' => 'updated@example.com',
        ],
    ]);

    $updateResponse->assertSuccessful()
        ->assertJsonPath('after.value.email', 'updated@example.com');

    $this->deleteJson(route('admin.settings.destroy', $settingId))
        ->assertSuccessful();

    expect(SiteSetting::query()->whereKey($settingId)->exists())->toBeFalse();
});

test('settings url validation rejects invalid linkedin github and whatsapp values', function (): void {
    $response = $this->postJson(route('admin.settings.store'), [
        'key' => 'social_links',
        'group' => 'contact',
        'value' => [
            'linkedin' => 'https://example.com/profile',
            'github' => 'https://gitlab.com/acme',
            'whatsapp' => 'https://t.me/12345',
        ],
    ]);

    $response->assertUnprocessable()
        ->assertInvalid([
            'value.linkedin',
            'value.github',
            'value.whatsapp',
        ]);
});

test('service crud and bulk reorder work', function (): void {
    $first = Service::factory()->create(['sort_order' => 10]);
    $second = Service::factory()->create(['sort_order' => 10]);

    $storeResponse = $this->postJson(route('admin.services.store'), [
        'title' => 'Backend Development',
        'description' => 'Build scalable Laravel APIs.',
        'icon' => 'server',
    ]);

    $storeResponse->assertCreated()
        ->assertJsonPath('data.title', 'Backend Development');

    $createdServiceId = $storeResponse->json('data.id');

    $this->getJson(route('admin.services.index'))
        ->assertSuccessful();

    $this->getJson(route('admin.services.show', $createdServiceId))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $createdServiceId);

    $this->putJson(route('admin.services.update', $createdServiceId), [
        'title' => 'Backend Architecture',
        'description' => 'Design and maintain APIs.',
        'icon' => 'database',
        'sort_order' => 4,
    ])->assertSuccessful()
        ->assertJsonPath('after.title', 'Backend Architecture');

    $reorderResponse = $this->patchJson(route('admin.services.reorder'), [
        'services' => [$second->id, $createdServiceId, $first->id],
    ]);

    $reorderResponse->assertSuccessful();

    expect($second->fresh()->sort_order)->toBe(0)
        ->and(Service::query()->findOrFail($createdServiceId)->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);

    $this->deleteJson(route('admin.services.destroy', $createdServiceId))
        ->assertSuccessful();
});

test('skill crud with category and bulk reorder work', function (): void {
    $first = Skill::factory()->create(['category' => 'backend', 'sort_order' => 10]);
    $second = Skill::factory()->create(['category' => 'frontend', 'sort_order' => 10]);

    $storeResponse = $this->postJson(route('admin.skills.store'), [
        'name' => 'Laravel',
        'category' => 'backend',
        'logo_path' => 'skills/laravel.svg',
    ]);

    $storeResponse->assertCreated()
        ->assertJsonPath('data.category', 'backend');

    $createdSkillId = $storeResponse->json('data.id');

    $this->getJson(route('admin.skills.index', ['category' => 'backend']))
        ->assertSuccessful()
        ->assertJsonPath('data.0.category', 'backend');

    $this->getJson(route('admin.skills.show', $createdSkillId))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $createdSkillId);

    $this->putJson(route('admin.skills.update', $createdSkillId), [
        'name' => 'React',
        'category' => 'frontend',
        'logo_path' => 'skills/react.svg',
        'sort_order' => 7,
    ])->assertSuccessful()
        ->assertJsonPath('after.category', 'frontend');

    $reorderResponse = $this->patchJson(route('admin.skills.reorder'), [
        'skills' => [$second->id, $createdSkillId, $first->id],
    ]);

    $reorderResponse->assertSuccessful();

    expect($second->fresh()->sort_order)->toBe(0)
        ->and(Skill::query()->findOrFail($createdSkillId)->sort_order)->toBe(1)
        ->and($first->fresh()->sort_order)->toBe(2);

    $this->deleteJson(route('admin.skills.destroy', $createdSkillId))
        ->assertSuccessful();
});

test('update responses include before and after structures', function (): void {
    $setting = SiteSetting::factory()->create([
        'key' => 'homepage_seo',
        'group' => 'seo',
        'value' => ['title' => 'Old Title'],
    ]);

    $service = Service::factory()->create([
        'title' => 'Old Service',
    ]);

    $skill = Skill::factory()->create([
        'name' => 'Old Skill',
        'category' => 'backend',
    ]);

    $this->putJson(route('admin.settings.update', $setting), [
        'key' => 'homepage_seo',
        'group' => 'seo',
        'value' => ['title' => 'New Title'],
    ])->assertSuccessful()
        ->assertJsonStructure(['message', 'before', 'after'])
        ->assertJsonPath('before.value.title', 'Old Title')
        ->assertJsonPath('after.value.title', 'New Title');

    $this->putJson(route('admin.services.update', $service), [
        'title' => 'New Service',
        'description' => 'Updated description',
        'icon' => 'code',
        'sort_order' => 2,
    ])->assertSuccessful()
        ->assertJsonStructure(['message', 'before', 'after'])
        ->assertJsonPath('before.title', 'Old Service')
        ->assertJsonPath('after.title', 'New Service');

    $this->putJson(route('admin.skills.update', $skill), [
        'name' => 'New Skill',
        'category' => 'frontend',
        'logo_path' => 'skills/new.svg',
        'sort_order' => 3,
    ])->assertSuccessful()
        ->assertJsonStructure(['message', 'before', 'after'])
        ->assertJsonPath('before.name', 'Old Skill')
        ->assertJsonPath('after.name', 'New Skill');
});
