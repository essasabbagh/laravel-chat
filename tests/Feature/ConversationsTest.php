<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;

class ConversationsTest extends TestCase
{
    private TestCustomer $customer;

    private TestAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = TestCustomer::create(['name' => 'Alice']);
        $this->agent = TestAgent::create(['name' => 'Bob']);
    }

    /** @test */
    public function can_create_a_direct_conversation()
    {
        $response = $this->postJson('/api/chat/conversations', [
            'type' => 'direct',
            'participants' => [
                ['type' => TestCustomer::class, 'id' => $this->customer->id, 'role' => 'member'],
                ['type' => TestAgent::class, 'id' => $this->agent->id, 'role' => 'member'],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'type', 'participants']);

        $this->assertDatabaseHas('chat_conversations', ['id' => $response->json('id')]);
        $this->assertDatabaseHas('chat_participants', ['conversation_id' => $response->json('id')]);
    }

    /** @test */
    public function can_list_conversations_for_a_participant()
    {
        $conversation = Conversation::factory()->create();
        $conversation->participants()->create([
            'participantable_type' => TestCustomer::class,
            'participantable_id' => $this->customer->id,
            'role' => 'member',
        ]);

        $response = $this->getJson('/api/chat/conversations?'.http_build_query([
            'participant_type' => TestCustomer::class,
            'participant_id' => $this->customer->id,
        ]));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function cannot_list_conversations_without_participant_params()
    {
        $response = $this->getJson('/api/chat/conversations');

        $response->assertStatus(422);
    }

    /** @test */
    public function can_show_a_conversation()
    {
        $conversation = Conversation::factory()->create();
        $conversation->participants()->create([
            'participantable_type' => TestCustomer::class,
            'participantable_id' => $this->customer->id,
            'role' => 'member',
        ]);

        $response = $this->getJson("/api/chat/conversations/{$conversation->id}");

        $response->assertStatus(200)
            ->assertJson(['id' => $conversation->id]);
    }

    /** @test */
    public function can_delete_a_conversation()
    {
        $conversation = Conversation::factory()->create();

        $response = $this->deleteJson("/api/chat/conversations/{$conversation->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted($conversation);
    }
}
