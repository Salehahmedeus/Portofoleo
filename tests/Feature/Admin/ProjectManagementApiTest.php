<?php

use App\Models\Project;
use App\Models\ProjectDetail;
use App\Models\ProjectImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
});

test('admin index supports search filter and sort for json requests', function (): void {
    Project::factory()->create([
        'title' => 'Alpha Project',
        'slug' => 'alpha-project',
        'type' => 'development',
        'summary' => 'Alpha summary',
    ]);

    Project::factory()->create([
        'title' => 'Gamma Project',
        'slug' => 'gamma-project',
        'type' => 'development',
        'summary' => 'Gamma summary',
    ]);

    Project::factory()->create([
        'title' => 'Uiux Project',
        'slug' => 'uiux-project',
        'type' => 'uiux',
        'summary' => 'Uiux summary',
    ]);

    $response = $this->getJson(route('admin.projects.index', [
        'search' => 'project',
        'type' => 'development',
        'sort' => '-title',
    ]));

    $response->assertSuccessful()
        ->assertJsonPath('filters.search', 'project')
        ->assertJsonPath('filters.type', 'development')
        ->assertJsonPath('filters.sort', '-title')
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.title', 'Gamma Project')
        ->assertJsonPath('data.1.title', 'Alpha Project');
});

test('admin show returns project edit payload as json', function (): void {
    $project = Project::factory()->create([
        'slug' => 'edit-project',
    ]);

    ProjectDetail::factory()->create([
        'project_id' => $project->id,
        'field_name' => 'client',
        'field_value' => ['name' => 'Acme Co'],
    ]);

    ProjectImage::factory()->create([
        'project_id' => $project->id,
        'image_path' => 'projects/gallery/edit-project.jpg',
        'sort_order' => 0,
    ]);

    $response = $this->getJson(route('admin.projects.show', $project));

    $response->assertSuccessful()
        ->assertJsonPath('data.slug', 'edit-project')
        ->assertJsonPath('data.details.client.field_name', 'client')
        ->assertJsonPath('data.details.client.field_value.name', 'Acme Co')
        ->assertJsonPath('data.images.0.image_path', 'projects/gallery/edit-project.jpg');
});

test('store auto generates a unique slug when slug is omitted', function (): void {
    Project::factory()->create([
        'title' => 'My Unique Project',
        'slug' => 'my-unique-project',
    ]);

    $response = $this->post(route('admin.projects.store'), [
        'title' => 'My Unique Project',
        'type' => 'development',
        'summary' => 'Project summary for slug generation test.',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('projects', [
        'title' => 'My Unique Project',
        'slug' => 'my-unique-project-2',
    ]);
});

test('store and update validate links urls and persist links detail', function (): void {
    $invalidResponse = $this->from(route('admin.projects.create'))->post(route('admin.projects.store'), [
        'title' => 'Invalid Links Project',
        'type' => 'development',
        'summary' => 'Summary',
        'links' => [
            'live_demo' => 'not-a-url',
            'github' => 'still-not-a-url',
        ],
    ]);

    $invalidResponse->assertRedirect(route('admin.projects.create'))
        ->assertInvalid(['links.live_demo', 'links.github']);

    $storeResponse = $this->post(route('admin.projects.store'), [
        'title' => 'Links Project',
        'type' => 'development',
        'summary' => 'Summary',
        'links' => [
            'live_demo' => 'https://example.com/demo',
            'github' => 'https://github.com/example/repo',
            'prototype' => 'https://figma.com/file/abc',
        ],
    ]);

    $storeResponse->assertRedirect();

    $project = Project::query()->where('title', 'Links Project')->firstOrFail();

    expect($project->details()->where('field_name', 'links')->exists())->toBeTrue();

    $linksDetail = $project->details()->where('field_name', 'links')->firstOrFail();

    expect($linksDetail->field_value)->toMatchArray([
        'live_demo' => 'https://example.com/demo',
        'github' => 'https://github.com/example/repo',
        'prototype' => 'https://figma.com/file/abc',
    ]);

    $updateResponse = $this->put(route('admin.projects.update', $project), [
        'title' => 'Links Project Updated',
        'slug' => $project->slug,
        'type' => 'development',
        'summary' => 'Updated summary',
        'details' => [],
        'links' => [
            'live_demo' => 'https://example.com/new-demo',
            'github' => null,
            'prototype' => null,
        ],
    ]);

    $updateResponse->assertRedirect();

    $project->refresh();

    $updatedLinksDetail = $project->details()->where('field_name', 'links')->firstOrFail();

    expect($updatedLinksDetail->field_value)->toMatchArray([
        'live_demo' => 'https://example.com/new-demo',
    ]);

    $removeLinksResponse = $this->put(route('admin.projects.update', $project), [
        'title' => 'Links Project Updated Again',
        'slug' => $project->slug,
        'type' => 'development',
        'summary' => 'Updated summary again',
        'details' => [],
        'links' => [
            'live_demo' => null,
            'github' => null,
            'prototype' => null,
        ],
    ]);

    $removeLinksResponse->assertRedirect();

    expect($project->fresh()->details()->where('field_name', 'links')->exists())->toBeFalse();
});

test('reorder endpoint updates project sort order', function (): void {
    $first = Project::factory()->create(['sort_order' => 99]);
    $second = Project::factory()->create(['sort_order' => 99]);
    $third = Project::factory()->create(['sort_order' => 99]);

    $response = $this->patchJson(route('admin.projects.reorder'), [
        'projects' => [$third->id, $first->id, $second->id],
    ]);

    $response->assertSuccessful();

    expect($third->fresh()->sort_order)->toBe(0)
        ->and($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(2);
});

test('toggle featured endpoint updates featured state', function (): void {
    $project = Project::factory()->create([
        'featured' => false,
    ]);

    $response = $this->patchJson(route('admin.projects.toggle-featured', $project), [
        'featured' => true,
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('featured', true);

    expect($project->fresh()->featured)->toBeTrue();
});

test('destroy image removes only project owned image and returns 404 for mismatch', function (): void {
    Storage::fake('public');

    $project = Project::factory()->create();
    $otherProject = Project::factory()->create();

    Storage::disk('public')->put('projects/gallery/owned.jpg', 'owned-image');
    Storage::disk('public')->put('projects/gallery/other.jpg', 'other-image');

    $ownedImage = ProjectImage::factory()->create([
        'project_id' => $project->id,
        'image_path' => 'projects/gallery/owned.jpg',
    ]);

    $otherImage = ProjectImage::factory()->create([
        'project_id' => $otherProject->id,
        'image_path' => 'projects/gallery/other.jpg',
    ]);

    $deleteOwnedResponse = $this->deleteJson(route('admin.projects.images.destroy', [
        'project' => $project,
        'image' => $ownedImage,
    ]));

    $deleteOwnedResponse->assertSuccessful();

    expect(ProjectImage::query()->whereKey($ownedImage->id)->exists())->toBeFalse();
    Storage::disk('public')->assertMissing('projects/gallery/owned.jpg');

    $deleteMismatchResponse = $this->deleteJson(route('admin.projects.images.destroy', [
        'project' => $project,
        'image' => $otherImage,
    ]));

    $deleteMismatchResponse->assertNotFound();

    expect(ProjectImage::query()->whereKey($otherImage->id)->exists())->toBeTrue();
    Storage::disk('public')->assertExists('projects/gallery/other.jpg');
});

test('store accepts mixed detail field value scalar types', function (): void {
    $response = $this->post(route('admin.projects.store'), [
        'title' => 'Mixed Details Project',
        'type' => 'uiux',
        'summary' => 'Summary',
        'details' => [
            ['field_name' => 'rating', 'field_value' => 5],
            ['field_name' => 'is_public', 'field_value' => true],
            ['field_name' => 'notes', 'field_value' => 'Ready'],
        ],
    ]);

    $response->assertRedirect();

    $project = Project::query()->where('title', 'Mixed Details Project')->firstOrFail();

    expect($project->details()->count())->toBe(3);
});

test('store image uploads are saved on configured disk', function (): void {
    Storage::fake('public');

    $response = $this->post(route('admin.projects.store'), [
        'title' => 'Project With Files',
        'type' => 'development',
        'summary' => 'Summary',
        'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg'),
        'images' => [
            [
                'file' => UploadedFile::fake()->image('gallery.jpg'),
                'alt_text' => 'Gallery image',
                'type' => 'gallery',
                'sort_order' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();

    $project = Project::query()->where('title', 'Project With Files')->firstOrFail();

    expect($project->thumbnail_path)->not->toBeNull();
    Storage::disk('public')->assertExists($project->thumbnail_path);

    $galleryImage = $project->images()->firstOrFail();
    Storage::disk('public')->assertExists($galleryImage->image_path);
});
