<?php

namespace Database\Factories;

use App\Enums\CardCondition;
use App\Enums\Edition;
use App\Enums\GradingCompany;
use App\Enums\Language;
use App\Enums\ListingStatus;
use App\Enums\ShippingMethod;
use App\Models\Card;
use App\Models\Listing;
use App\Models\ListingPhoto;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;

/**
 * Par défaut : une annonce disponible, non gradée, sans photo.
 *
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seller_id' => User::factory()->seller(),
            'card_id' => Card::factory(),
            'order_id' => null,
            'description' => fake()->optional()->sentence(),
            'price' => fake()->numberBetween(100, 50_000), // en centimes (RG-09)
            'card_condition' => fake()->randomElement(CardCondition::cases()),
            'edition' => fake()->randomElement(Edition::cases()),
            'language' => fake()->randomElement(Language::cases()),
            'is_graded' => false,
            'grading_company' => null,
            'grade' => null,
            'shipping_method' => fake()->randomElement(ShippingMethod::cases()),
            'status' => ListingStatus::Available,
        ];
    }

    /**
     * Carte gradée : la note remplace l'état (RG-07).
     */
    public function graded(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_graded' => true,
            'card_condition' => null,
            'grading_company' => fake()->randomElement(GradingCompany::cases()),
            'grade' => fake()->randomElement([7, 7.5, 8, 8.5, 9, 9.5, 10]),
        ]);
    }

    /**
     * Ajoute des photos aux positions 1, 2, … (2 minimum, RG-08).
     */
    public function withPhotos(int $count = 2): static
    {
        return $this->has(
            ListingPhoto::factory()
                ->count($count)
                ->state(new Sequence(fn (Sequence $sequence) => ['position' => $sequence->index + 1])),
            'photos'
        );
    }

    public function reserved(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ListingStatus::Reserved]);
    }

    /**
     * Annonce vendue, rattachée à une commande.
     */
    public function sold(?Order $order = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ListingStatus::Sold,
            'order_id' => $order ?? Order::factory(),
        ]);
    }

    public function withdrawn(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ListingStatus::Withdrawn]);
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => ['status' => ListingStatus::Hidden]);
    }
}
