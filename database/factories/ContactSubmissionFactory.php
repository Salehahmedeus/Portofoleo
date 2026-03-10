<?php

namespace Database\Factories;

use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    protected $model = ContactSubmission::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subject' => fake()->optional()->sentence(4),
            'message' => fake()->paragraph(3),
            'read' => fake()->boolean(15),
            'ip_address' => fake()->optional()->ipv4(),
        ];
    }
}
