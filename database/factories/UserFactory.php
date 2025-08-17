<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    private static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'avatar' => null,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'status' => fake()->randomElement(\App\Enums\UserStatus::cases())->value,
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('secret'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is active.
     */
    public function active(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => \App\Enums\UserStatus::ACTIVE,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => \App\Enums\UserStatus::INACTIVE,
        ]);
    }

    /**
     * Indicate that the user is suspended.
     */
    public function suspended(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => \App\Enums\UserStatus::SUSPENDED,
        ]);
    }
}
