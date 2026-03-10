<?php

use App\Models\Project;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\seed;

it('has the expected portfolio tables and columns', function () {
    $expectedSchema = [
        'projects' => [
            'id',
            'title',
            'slug',
            'type',
            'summary',
            'featured',
            'sort_order',
            'thumbnail_path',
            'meta_title',
            'meta_description',
            'created_at',
            'updated_at',
        ],
        'project_details' => [
            'id',
            'project_id',
            'field_name',
            'field_value',
        ],
        'project_images' => [
            'id',
            'project_id',
            'image_path',
            'alt_text',
            'sort_order',
            'type',
            'created_at',
            'updated_at',
        ],
        'services' => [
            'id',
            'title',
            'description',
            'icon',
            'sort_order',
            'created_at',
            'updated_at',
        ],
        'skills' => [
            'id',
            'name',
            'category',
            'logo_path',
            'sort_order',
            'created_at',
            'updated_at',
        ],
        'site_settings' => [
            'id',
            'key',
            'value',
            'group',
            'created_at',
            'updated_at',
        ],
        'contact_submissions' => [
            'id',
            'name',
            'email',
            'subject',
            'message',
            'read',
            'ip_address',
            'created_at',
            'updated_at',
        ],
        'analytics_events' => [
            'id',
            'event_type',
            'event_data',
            'page_url',
            'referrer',
            'device_type',
            'country',
            'ip_address',
            'session_id',
            'created_at',
            'updated_at',
        ],
    ];

    foreach ($expectedSchema as $table => $columns) {
        expect(Schema::hasTable($table))->toBeTrue();

        foreach ($columns as $column) {
            expect(Schema::hasColumn($table, $column))->toBeTrue();
        }
    }
});

it('cascades project delete to details and images', function () {
    $project = Project::factory()->create();

    $detail = $project->details()->create([
        'field_name' => 'client',
        'field_value' => ['label' => 'Client', 'value' => 'Acme Inc'],
    ]);

    $image = $project->images()->create([
        'image_path' => 'projects/test-image.jpg',
        'alt_text' => 'Test image',
        'sort_order' => 0,
        'type' => 'gallery',
    ]);

    $project->delete();

    assertDatabaseMissing('projects', ['id' => $project->id]);
    assertDatabaseMissing('project_details', ['id' => $detail->id]);
    assertDatabaseMissing('project_images', ['id' => $image->id]);
});

it('seeds representative portfolio content', function () {
    seed(PortfolioContentSeeder::class);

    assertDatabaseHas('projects', ['slug' => 'nova-commerce-platform']);
    assertDatabaseHas('projects', ['slug' => 'pulse-finance-app-redesign']);
    assertDatabaseHas('site_settings', ['key' => 'hero_content']);
    assertDatabaseHas('site_settings', ['key' => 'contact_information']);

    expect(Project::query()->count())->toBeGreaterThanOrEqual(2)
        ->and(Project::query()->first()->details()->count())->toBeGreaterThan(0)
        ->and(Project::query()->first()->images()->count())->toBeGreaterThan(0);
});

it('uses slug for project route model binding', function () {
    $project = Project::factory()->create([
        'slug' => 'custom-route-key-slug',
    ]);

    expect($project->getRouteKeyName())->toBe('slug')
        ->and($project->getRouteKey())->toBe('custom-route-key-slug');
});
