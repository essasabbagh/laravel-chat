<?php

namespace Essasabbagh\LaravelChat\Database\Factories;

use Essasabbagh\LaravelChat\Models\Attachment;
use Essasabbagh\LaravelChat\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'type' => 'image',
            'path' => 'attachments/'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(1000, 500000),
        ];
    }
}
