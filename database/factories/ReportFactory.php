<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Models\Comment;
use App\Models\Listing;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un signalement en attente visant une annonce.
 *
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reporter_id' => User::factory(),
            'listing_id' => Listing::factory(),
            'comment_id' => null,
            'reason' => fake()->randomElement(['Contrefaçon', 'Photos trompeuses', 'Contenu inapproprié', 'Arnaque']),
            'description' => fake()->optional()->sentence(),
            'status' => ReportStatus::Pending,
        ];
    }

    /**
     * Signalement visant un commentaire plutôt qu'une annonce (contrainte XT).
     */
    public function forComment(?Comment $comment = null): static
    {
        return $this->state(fn (array $attributes) => [
            'listing_id' => null,
            'comment_id' => $comment ?? Comment::factory(),
        ]);
    }
}
