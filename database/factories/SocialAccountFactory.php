<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SocialAccount>
 */
final class SocialAccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider_name' => 'google',
            'provider_id' => fake()->unique()->numerify('##########'),
            'avatar' => fake()->imageUrl(),
            'user_id' => \App\Models\User::factory(),
        ];
    }
}
