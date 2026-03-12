<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Config::set('filesystems.default', 'public');
    Storage::fake('public');

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);
});

test('upload stores generated variants and project image metadata entries', function (): void {
    $response = $this->post(route('admin.projects.store'), [
        'title' => 'Milestone Nine Project',
        'type' => 'development',
        'summary' => 'Image processing flow should create WebP variants.',
        'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 1800, 1200),
        'images' => [
            [
                'file' => UploadedFile::fake()->image('gallery.png', 1920, 1080),
                'alt_text' => 'Hero gallery shot',
                'type' => 'gallery',
                'sort_order' => 0,
            ],
        ],
    ]);

    $response->assertRedirect();

    $project = Project::query()->where('title', 'Milestone Nine Project')->firstOrFail();
    $image = $project->images()->firstOrFail();

    expect($project->thumbnail_path)->toEndWith('_large.webp');
    expect($image->disk)->toBe('public')
        ->and($image->image_path)->toEndWith('_large.webp')
        ->and($image->variants)->toBeArray()
        ->and($image->metadata)->toBeArray();

    $thumbnailVariants = [
        str_replace('_large.webp', '_thumbnail.webp', $project->thumbnail_path),
        str_replace('_large.webp', '_medium.webp', $project->thumbnail_path),
        $project->thumbnail_path,
    ];

    foreach ($thumbnailVariants as $path) {
        Storage::disk('public')->assertExists($path);
    }

    foreach ($image->variants as $path) {
        Storage::disk('public')->assertExists($path);
    }

    $apiResponse = $this->getJson(route('api.projects.show', ['project' => $project->slug]));

    $apiResponse->assertSuccessful()
        ->assertJsonPath('data.thumbnail_url', Storage::disk('public')->url($project->thumbnail_path))
        ->assertJsonPath('data.images.0.image_url', Storage::disk('public')->url($image->image_path))
        ->assertJsonPath('data.images.0.image_urls.large', Storage::disk('public')->url($image->variants['large']));
});

test('deletion removes generated variants', function (): void {
    $storeResponse = $this->post(route('admin.projects.store'), [
        'title' => 'Delete Variants Project',
        'type' => 'uiux',
        'summary' => 'Variant cleanup should remove generated files.',
        'thumbnail' => UploadedFile::fake()->image('thumbnail.jpg', 1600, 900),
        'images' => [
            [
                'file' => UploadedFile::fake()->image('gallery.jpg', 1600, 900),
                'type' => 'gallery',
                'sort_order' => 0,
            ],
        ],
    ]);

    $storeResponse->assertRedirect();

    $project = Project::query()->where('title', 'Delete Variants Project')->firstOrFail();
    $image = $project->images()->firstOrFail();

    $galleryVariantPaths = array_values($image->variants ?? []);

    $this->deleteJson(route('admin.projects.images.destroy', ['project' => $project, 'image' => $image]))
        ->assertSuccessful();

    foreach ($galleryVariantPaths as $path) {
        Storage::disk('public')->assertMissing($path);
    }

    $thumbnailVariants = [
        str_replace('_large.webp', '_thumbnail.webp', $project->thumbnail_path),
        str_replace('_large.webp', '_medium.webp', $project->thumbnail_path),
        $project->thumbnail_path,
    ];

    $this->delete(route('admin.projects.destroy', $project))->assertRedirect();

    foreach ($thumbnailVariants as $path) {
        Storage::disk('public')->assertMissing($path);
    }

    $this->assertDatabaseMissing('projects', ['id' => $project->id]);
});
