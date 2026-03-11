<?php

use App\Models\Project;
use Database\Seeders\PortfolioContentSeeder;
use Illuminate\Support\Facades\Cache;

use function Pest\Laravel\seed;

it('returns expected home sections from seeded content', function () {
    seed(PortfolioContentSeeder::class);
    Cache::flush();

    $response = $this->getJson(route('api.home'));

    $response->assertSuccessful()
        ->assertJsonStructure([
            'hero_content',
            'featured_projects',
            'services',
            'skills',
            'contact_info',
            'social_links',
        ])
        ->assertJsonPath('hero_content.headline', 'Building thoughtful digital products')
        ->assertJsonPath('contact_info.email', 'hello@portfolio.test')
        ->assertJsonPath('social_links.linkedin', 'https://linkedin.com/in/portfolio')
        ->assertJsonPath('featured_projects.0.slug', 'nova-commerce-platform');
});

it('returns project cards and omits nullable null fields', function () {
    Cache::flush();

    Project::factory()->create([
        'title' => 'Card Only Project',
        'slug' => 'card-only-project',
        'type' => 'development',
        'summary' => 'Card data only response.',
        'thumbnail_path' => null,
        'sort_order' => 1,
    ]);

    $response = $this->getJson(route('api.projects.index'));

    $response->assertSuccessful()
        ->assertJsonPath('data.0.title', 'Card Only Project')
        ->assertJsonPath('data.0.slug', 'card-only-project')
        ->assertJsonPath('data.0.type', 'development')
        ->assertJsonPath('data.0.summary', 'Card data only response.');

    $projectCard = $response->json('data.0');

    expect($projectCard)
        ->toHaveKeys(['id', 'title', 'slug', 'type', 'summary'])
        ->not->toHaveKey('thumbnail_path')
        ->not->toHaveKey('thumbnail_url');
});

it('returns full project details and images through slug binding', function () {
    seed(PortfolioContentSeeder::class);
    Cache::flush();

    $response = $this->getJson(route('api.projects.show', ['project' => 'nova-commerce-platform']));

    $response->assertSuccessful()
        ->assertJsonPath('data.slug', 'nova-commerce-platform')
        ->assertJsonPath('data.details.client.field_name', 'client')
        ->assertJsonPath('data.images.0.image_path', 'projects/nova-commerce/cover.jpg');
});

it('returns not found json for unknown project slug', function () {
    $response = $this->getJson(route('api.projects.show', ['project' => 'missing-project-slug']));

    $response->assertNotFound()
        ->assertJsonStructure(['message']);
});

it('caches public home response payload', function () {
    seed(PortfolioContentSeeder::class);
    Cache::flush();

    expect(Cache::has('public_api:home'))->toBeFalse();

    $this->getJson(route('api.home'))->assertSuccessful();

    expect(Cache::has('public_api:home'))->toBeTrue();
});
