<?php

namespace Essasabbagh\LaravelChat\Database\Factories;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'participantable_type' => 'dummy_user',
            'participantable_id' => 1,
            'role' => 'member',
            'joined_at' => now(),
        ];
    }
}
