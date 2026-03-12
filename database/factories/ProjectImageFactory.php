<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectImage>
 */
class ProjectImageFactory extends Factory
{
    protected $model = ProjectImage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'image_path' => 'projects/'.fake()->uuid().'_large.webp',
            'disk' => 'public',
            'alt_text' => fake()->optional()->sentence(6),
            'sort_order' => fake()->numberBetween(0, 12),
            'type' => fake()->randomElement(['thumbnail', 'gallery', 'wireframe', 'screenshot']),
            'variants' => null,
            'metadata' => null,
        ];
    }
}
