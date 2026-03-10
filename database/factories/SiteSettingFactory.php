<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'setting_'.fake()->unique()->word(),
            'value' => [
                'label' => fake()->words(2, true),
                'content' => fake()->sentence(),
            ],
            'group' => fake()->randomElement(['general', 'hero', 'contact', 'seo']),
        ];
    }
}
