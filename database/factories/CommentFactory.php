<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'listing_id' => Listing::factory(),
            'user_id' => User::factory(),
            'parent_id' => null,
            'content' => fake()->sentence(),
            'is_hidden' => false,
            'hidden_reason' => null,
        ];
    }

    /**
     * Réponse à un autre commentaire, sur la même annonce.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_id' => $parent->listing_id,
            'parent_id' => $parent->id,
        ]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_hidden' => true,
            'hidden_reason' => 'Propos injurieux',
        ]);
    }
}
