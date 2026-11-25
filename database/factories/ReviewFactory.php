<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->completed(), // RG-28 : seule une commande terminée est notée
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake()->optional()->sentence(),
            'seller_reply' => null,
            'seller_replied_at' => null,
        ];
    }

    public function withSellerReply(): static
    {
        return $this->state(fn (array $attributes) => [
            'seller_reply' => fake()->sentence(),
            'seller_replied_at' => now(),
        ]);
    }
}
