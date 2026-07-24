<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Contracts\PresenceDriver;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Services\AdminChatService;
use Essasabbagh\LaravelChat\Tests\TestCase;

class AdminChatServiceTest extends TestCase
{
    private AdminChatService $adminService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminService = $this->app->make(AdminChatService::class);
    }

    /** @test */
    public function can_block_and_unblock_participant()
    {
        config(['chat.admin.allow_block' => true]);

        $block = $this->adminService->blockParticipant('user', '1', 'user', '2');

        $this->assertDatabaseHas('chat_blocks', [
            'blocker_type' => 'user',
            'blocker_id' => '1',
            'blocked_type' => 'user',
            'blocked_id' => '2',
        ]);

        $this->adminService->unblockParticipant('user', '1', 'user', '2');

        $this->assertDatabaseMissing('chat_blocks', [
            'blocker_type' => 'user',
            'blocker_id' => '1',
            'blocked_type' => 'user',
            'blocked_id' => '2',
        ]);
    }

    /** @test */
    public function blocking_disabled_throws_exception()
    {
        config(['chat.admin.allow_block' => false]);

        $this->expectException(\RuntimeException::class);

        $this->adminService->blockParticipant('user', '1', 'user', '2');
    }

    /** @test */
    public function unblocking_disabled_throws_exception()
    {
        config(['chat.admin.allow_block' => false]);

        $this->expectException(\RuntimeException::class);

        $this->adminService->unblockParticipant('user', '1', 'user', '2');
    }

    /** @test */
    public function can_force_user_offline()
    {
        config(['chat.admin.allow_force_offline' => true]);

        $presence = $this->createMock(PresenceDriver::class);
        $presence->expects($this->once())
            ->method('offline')
            ->with('1', 'user');

        $this->app->instance(PresenceDriver::class, $presence);

        $service = $this->app->make(AdminChatService::class);
        $service->forceOffline('user', '1');
    }

    /** @test */
    public function force_offline_disabled_throws_exception()
    {
        config(['chat.admin.allow_force_offline' => false]);

        $this->expectException(\RuntimeException::class);

        $this->adminService->forceOffline('user', '1');
    }

    /** @test */
    public function can_delete_message()
    {
        config(['chat.admin.allow_delete' => true]);

        $conversation = Conversation::factory()->create();
        $message = Message::factory()->create(['conversation_id' => $conversation->id]);

        $this->adminService->deleteMessage($message);

        $this->assertDatabaseMissing('chat_messages', ['id' => $message->id]);
    }

    /** @test */
    public function can_delete_conversation()
    {
        config(['chat.admin.allow_delete' => true]);

        $conversation = Conversation::factory()->create();

        $this->adminService->deleteConversation($conversation);

        $this->assertDatabaseMissing('chat_conversations', ['id' => $conversation->id]);
    }

    /** @test */
    public function delete_disabled_throws_exception()
    {
        config(['chat.admin.allow_delete' => false]);

        $this->expectException(\RuntimeException::class);

        $conversation = Conversation::factory()->create();
        $this->adminService->deleteConversation($conversation);
    }

    /** @test */
    public function can_change_user_status()
    {
        config(['chat.admin.allow_status_change' => true]);

        $this->adminService->changeUserStatus('user', '1', 'away');

        $this->assertDatabaseHas('chat_user_status', [
            'user_type' => 'user',
            'user_id' => '1',
            'status' => 'away',
        ]);
    }

    /** @test */
    public function status_change_disabled_throws_exception()
    {
        config(['chat.admin.allow_status_change' => false]);

        $this->expectException(\RuntimeException::class);

        $this->adminService->changeUserStatus('user', '1', 'away');
    }
}
