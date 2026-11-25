<?php

namespace Database\Factories;

use App\Enums\DisputeDecision;
use App\Enums\DisputeReason;
use App\Enums\DisputeStatus;
use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut : un litige ouvert sur une commande en litige.
 *
 * @extends Factory<Dispute>
 */
class DisputeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->disputed(),
            'moderator_id' => null,
            'reason' => fake()->randomElement(DisputeReason::cases()),
            'description' => fake()->paragraph(),
            'status' => DisputeStatus::Open,
            'decision' => null,
            'justification' => null,
            'resolved_at' => null,
        ];
    }

    /**
     * Litige tranché par un modérateur (RG-26).
     */
    public function resolved(DisputeDecision $decision = DisputeDecision::RefundBuyer): static
    {
        return $this->state(fn (array $attributes) => [
            'moderator_id' => User::factory()->moderator(),
            'status' => DisputeStatus::Resolved,
            'decision' => $decision,
            'justification' => fake()->sentence(),
            'resolved_at' => now(),
        ]);
    }
}
