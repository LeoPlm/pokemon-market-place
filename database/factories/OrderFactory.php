<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Par défaut : une commande payée, pas encore expédiée.
 * Les montants sont de simples valeurs de test cohérentes (total = somme des trois) :
 * le vrai calcul des frais (RG-14, RG-15) sera fait par un service dédié, à partir de la configuration.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(100, 50_000);
        $shippingFee = 200;
        $protectionFee = 70 + (int) round($subtotal * 0.05);

        return [
            'buyer_id' => User::factory(),
            'subtotal' => $subtotal,
            'shipping_fee' => $shippingFee,
            'protection_fee' => $protectionFee,
            'total' => $subtotal + $shippingFee + $protectionFee,
            'status' => OrderStatus::Paid,
            'tracking_number' => null,
            'stripe_payment_intent_id' => 'pi_'.Str::random(24),
            'paid_at' => now(),
            'shipped_at' => null,
            'received_at' => null,
            'shipping_first_name' => fake()->firstName(),
            'shipping_last_name' => fake()->lastName(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_postal_code' => fake()->postcode(),
            'shipping_city' => fake()->city(),
            'shipping_country' => 'FR',
        ];
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped,
            'tracking_number' => strtoupper(Str::random(13)),
            'shipped_at' => now(),
        ]);
    }

    public function disputed(): static
    {
        return $this->shipped()->state(fn (array $attributes) => [
            'status' => OrderStatus::Disputed,
        ]);
    }

    public function completed(): static
    {
        return $this->shipped()->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
            'received_at' => now(),
        ]);
    }

    public function refunded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Refunded,
        ]);
    }
}
