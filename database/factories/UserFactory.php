<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->regexify('[a-z]{3}[a-z0-9_]{5,12}'), // RG-01 : 3 à 20 caractères
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'address' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'city' => fake()->city(),
            'country' => 'FR',
            'role' => UserRole::User,
            'suspended_at' => null,
            'stripe_account_id' => null,
            'stripe_account_active' => false,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Vendeur dont le compte Stripe Connect est activé (RG-02).
     */
    public function seller(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_account_id' => 'acct_'.Str::random(16),
            'stripe_account_active' => true,
        ]);
    }

    public function moderator(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Moderator,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'suspended_at' => now(),
        ]);
    }
}
