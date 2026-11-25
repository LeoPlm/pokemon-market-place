<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Par défaut, le message est envoyé par l'acheteur de la conversation.
 *
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => fn (array $attributes) => Conversation::findOrFail($attributes['conversation_id'])->buyer_id,
            'content' => fake()->sentence(),
            'read_at' => null,
        ];
    }

    /**
     * Message envoyé par le vendeur de l'annonce.
     */
    public function fromSeller(): static
    {
        return $this->state([
            'sender_id' => fn (array $attributes) => Conversation::findOrFail($attributes['conversation_id'])->listing->seller_id,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => ['read_at' => now()]);
    }
}
