<?php

namespace Database\Factories;

use App\Models\AnalyticsEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AnalyticsEvent>
 */
class AnalyticsEventFactory extends Factory
{
    protected $model = AnalyticsEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_type' => fake()->randomElement(['page_view', 'outbound_click', 'contact_submit']),
            'event_data' => [
                'label' => fake()->words(2, true),
                'value' => fake()->word(),
            ],
            'page_url' => fake()->url(),
            'referrer' => fake()->optional()->url(),
            'device_type' => fake()->optional()->randomElement(['desktop', 'mobile', 'tablet']),
            'country' => fake()->optional()->countryCode(),
            'ip_address' => fake()->optional()->ipv4(),
            'session_id' => fake()->optional()->regexify('[A-Za-z0-9]{32}'),
        ];
    }
}
