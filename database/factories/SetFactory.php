<?php

namespace Database\Factories;

use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Extensions de test. Les vraies extensions Gen 1 viendront de l'import du catalogue.
 *
 * @extends Factory<Set>
 */
class SetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'released_at' => fake()->dateTimeBetween('1999-01-01', '2000-12-31'),
            'logo' => null,
        ];
    }
}
