<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;

class MessagesTest extends TestCase
{
    private TestCustomer $customer;

    private TestAgent $agent;

    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = TestCustomer::create(['name' => 'Alice']);
        $this->agent = TestAgent::create(['name' => 'Bob']);

        $this->conversation = Conversation::factory()->create();
        $this->conversation->participants()->createMany([
            [
                'participantable_type' => TestCustomer::class,
                'participantable_id' => $this->customer->id,
                'role' => 'member',
            ],
            [
                'participantable_type' => TestAgent::class,
                'participantable_id' => $this->agent->id,
                'role' => 'member',
            ],
        ]);
    }

    /** @test */
    public function can_send_a_message()
    {
        $response = $this->postJson("/api/chat/conversations/{$this->conversation->id}/messages", [
            'body' => 'Hello, world!',
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'body', 'sender']);

        $this->assertDatabaseHas('chat_messages', [
            'conversation_id' => $this->conversation->id,
            'body' => 'Hello, world!',
        ]);
    }

    /** @test */
    public function can_list_messages()
    {
        Message::factory()->count(3)->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);

        $response = $this->getJson("/api/chat/conversations/{$this->conversation->id}/messages");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /** @test */
    public function can_show_a_message()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
            'body' => 'Test message',
        ]);

        $response = $this->getJson("/api/chat/conversations/{$this->conversation->id}/messages/{$message->id}");

        $response->assertStatus(200)
            ->assertJson(['body' => 'Test message']);
    }

    /** @test */
    public function can_delete_a_message()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $response = $this->deleteJson("/api/chat/conversations/{$this->conversation->id}/messages/{$message->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($message);
    }
}
