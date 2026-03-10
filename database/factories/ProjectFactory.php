<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'title' => $title,
            'slug' => fake()->unique()->slug(3),
            'type' => fake()->randomElement(['development', 'uiux']),
            'summary' => fake()->paragraph(3),
            'featured' => fake()->boolean(20),
            'sort_order' => fake()->numberBetween(0, 25),
            'thumbnail_path' => fake()->optional()->filePath(),
            'meta_title' => fake()->optional()->sentence(6),
            'meta_description' => fake()->optional()->sentence(12),
        ];
    }
}
