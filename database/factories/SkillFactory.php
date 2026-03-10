<?php

namespace Database\Factories;

use App\Models\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Laravel', 'React', 'TypeScript', 'Tailwind CSS', 'Figma', 'PostgreSQL']),
            'category' => fake()->randomElement(['backend', 'frontend', 'design', 'database']),
            'logo_path' => fake()->optional()->randomElement([
                'skills/laravel.svg',
                'skills/react.svg',
                'skills/figma.svg',
            ]),
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}
