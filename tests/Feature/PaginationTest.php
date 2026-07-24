<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;

class PaginationTest extends TestCase
{
    private TestCustomer $customer;

    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = TestCustomer::create(['name' => 'Alice']);

        $this->conversation = Conversation::factory()->create();
        $this->conversation->participants()->create([
            'participantable_type' => TestCustomer::class,
            'participantable_id' => $this->customer->id,
            'role' => 'member',
        ]);
    }

    /** @test */
    public function messages_are_cursor_paginated()
    {
        Message::factory()->count(10)->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);

        $response = $this->getJson(
            "/api/chat/conversations/{$this->conversation->id}/messages?per_page=5"
        );

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    /** @test */
    public function concurrent_insert_does_not_cause_duplicates()
    {
        $existingIds = [];

        for ($i = 0; $i < 5; $i++) {
            $msg = $this->conversation->messages()->create([
                'sender_type' => TestCustomer::class,
                'sender_id' => $this->customer->id,
                'body' => "Message {$i}",
            ]);
            $existingIds[] = $msg->id;
        }

        Message::insert([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
            'body' => 'Concurrent insert',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Message::insert([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
            'body' => 'Another concurrent',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->getJson(
            "/api/chat/conversations/{$this->conversation->id}/messages?per_page=20"
        );

        $response->assertStatus(200);

        $messageIds = collect($response->json('data'))->pluck('id')->unique();
        $this->assertCount($messageIds->count(), $messageIds);
    }

    /** @test */
    public function conversations_are_cursor_paginated()
    {
        Conversation::factory()->count(5)->create()->each(function ($c) {
            $c->participants()->create([
                'participantable_type' => TestCustomer::class,
                'participantable_id' => $this->customer->id,
                'role' => 'member',
            ]);
        });

        $response = $this->getJson('/api/chat/conversations?'.http_build_query([
            'participant_type' => TestCustomer::class,
            'participant_id' => $this->customer->id,
            'per_page' => 3,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
    }
}
