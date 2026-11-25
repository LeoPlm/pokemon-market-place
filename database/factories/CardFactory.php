<?php

namespace Database\Factories;

use App\Models\Card;
use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = (string) fake()->unique()->numberBetween(1, 9999);

        return [
            'set_id' => Set::factory(),
            'name' => fake()->randomElement(['Charizard', 'Blastoise', 'Venusaur', 'Pikachu', 'Mewtwo', 'Gyarados', 'Alakazam', 'Dragonite']),
            'number' => $number,
            'rarity' => fake()->randomElement(['Common', 'Uncommon', 'Rare', 'Rare Holo']),
            'image' => null,
            'external_id' => 'test-'.$number,
        ];
    }
}
