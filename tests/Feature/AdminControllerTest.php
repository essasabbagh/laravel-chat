<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Tests\TestCase;

class AdminControllerTest extends TestCase
{
    /** @test */
    public function block_endpoint_returns_block()
    {
        $response = $this->postJson('/api/chat/admin/block', [
            'blocker_type' => 'user',
            'blocker_id' => '1',
            'blocked_type' => 'user',
            'blocked_id' => '2',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('chat_blocks', [
            'blocker_type' => 'user',
            'blocker_id' => '1',
            'blocked_type' => 'user',
            'blocked_id' => '2',
        ]);
    }

    /** @test */
    public function unblock_endpoint_returns_204()
    {
        $response = $this->postJson('/api/chat/admin/unblock', [
            'blocker_type' => 'user',
            'blocker_id' => '1',
            'blocked_type' => 'user',
            'blocked_id' => '2',
        ]);

        $response->assertStatus(204);
    }

    /** @test */
    public function force_offline_endpoint_returns_204()
    {
        $response = $this->postJson('/api/chat/admin/users/force-offline', [
            'user_type' => 'user',
            'user_id' => '1',
        ]);

        $response->assertStatus(204);
    }

    /** @test */
    public function delete_message_endpoint_returns_204()
    {
        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        $response = $this->deleteJson("/api/chat/admin/conversations/{$conversation->id}/messages/{$message->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    /** @test */
    public function delete_conversation_endpoint_returns_204()
    {
        $conversation = Conversation::factory()->create();

        $response = $this->deleteJson("/api/chat/admin/conversations/{$conversation->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat_conversations', ['id' => $conversation->id]);
    }

    /** @test */
    public function change_status_endpoint_returns_204()
    {
        $response = $this->postJson('/api/chat/admin/users/status', [
            'user_type' => 'user',
            'user_id' => '1',
            'status' => 'away',
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseHas('chat_user_status', [
            'user_type' => 'user',
            'user_id' => '1',
            'status' => 'away',
        ]);
    }

    /** @test */
    public function block_returns_403_when_disabled()
    {
        config(['chat.admin.allow_block' => false]);

        $response = $this->postJson('/api/chat/admin/block', [
            'blocker_type' => 'user',
            'blocker_id' => '1',
            'blocked_type' => 'user',
            'blocked_id' => '2',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function delete_returns_403_when_disabled()
    {
        config(['chat.admin.allow_delete' => false]);

        $conversation = Conversation::factory()->create();

        $response = $this->deleteJson("/api/chat/admin/conversations/{$conversation->id}");

        $response->assertStatus(403);
    }
}
