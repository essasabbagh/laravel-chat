<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;

class RepliesAndReactionsTest extends TestCase
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
            ['participantable_type' => TestCustomer::class, 'participantable_id' => $this->customer->id, 'role' => 'member'],
            ['participantable_type' => TestAgent::class, 'participantable_id' => $this->agent->id, 'role' => 'member'],
        ]);
    }

    /** @test */
    public function can_reply_to_a_message()
    {
        $original = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
            'body' => 'Original message',
        ]);

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages",
            [
                'body' => 'Reply message',
                'sender_type' => TestAgent::class,
                'sender_id' => $this->agent->id,
                'reply_to_id' => $original->id,
            ]
        );

        $response->assertStatus(201);
        $this->assertEquals($original->id, $response->json('reply_to_id'));
    }

    /** @test */
    public function can_reply_to_deleted_message()
    {
        $original = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'body' => 'Will be deleted',
        ]);
        $original->delete();

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages",
            [
                'body' => 'Reply after delete',
                'sender_type' => TestCustomer::class,
                'sender_id' => $this->customer->id,
                'reply_to_id' => $original->id,
            ]
        );

        $response->assertStatus(201);
        $this->assertEquals($original->id, $response->json('reply_to_id'));
    }

    /** @test */
    public function can_add_reaction()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$message->id}/reactions",
            [
                'emoji' => '👍',
                'reactor_type' => TestCustomer::class,
                'reactor_id' => $this->customer->id,
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('chat_reactions', [
            'message_id' => $message->id,
            'emoji' => '👍',
        ]);
    }

    /** @test */
    public function toggle_same_emoji_removes_reaction()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$message->id}/reactions",
            [
                'emoji' => '👍',
                'reactor_type' => TestCustomer::class,
                'reactor_id' => $this->customer->id,
            ]
        );

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$message->id}/reactions",
            [
                'emoji' => '👍',
                'reactor_type' => TestCustomer::class,
                'reactor_id' => $this->customer->id,
            ]
        );

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat_reactions', [
            'message_id' => $message->id,
            'emoji' => '👍',
        ]);
    }

    /** @test */
    public function can_delete_reaction()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $reaction = $message->reactions()->create([
            'emoji' => '❤️',
            'reactor_type' => TestCustomer::class,
            'reactor_id' => $this->customer->id,
        ]);

        $response = $this->deleteJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$message->id}/reactions/{$reaction->id}"
        );

        $response->assertStatus(204);
        $this->assertModelMissing($reaction);
    }
}
