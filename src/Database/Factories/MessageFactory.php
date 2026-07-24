<?php

namespace Essasabbagh\LaravelChat\Database\Factories;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_type' => 'dummy_user',
            'sender_id' => 1,
            'body' => fake()->sentence(),
        ];
    }
}
