<?php

namespace Essasabbagh\LaravelChat\Database\Factories;

use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Models\Reaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReactionFactory extends Factory
{
    protected $model = Reaction::class;

    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'reactor_type' => 'dummy_user',
            'reactor_id' => 1,
            'emoji' => fake()->randomElement(['👍', '❤️', '😂', '😮']),
        ];
    }
}
