<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectDetail>
 */
class ProjectDetailFactory extends Factory
{
    protected $model = ProjectDetail::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'field_name' => fake()->randomElement(['client', 'duration', 'role', 'stack', 'challenge', 'solution']),
            'field_value' => fake()->optional()->randomElement([
                ['label' => 'Client', 'value' => fake()->company()],
                ['label' => 'Duration', 'value' => fake()->numberBetween(2, 16).' weeks'],
                ['label' => 'Role', 'value' => fake()->jobTitle()],
            ]),
        ];
    }
}
