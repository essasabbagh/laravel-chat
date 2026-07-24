<?php

namespace Essasabbagh\LaravelChat\Database\Factories;

use Essasabbagh\LaravelChat\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'type' => 'direct',
            'name' => null,
        ];
    }

    public function group(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'group',
            'name' => fake()->word(),
        ]);
    }
}
