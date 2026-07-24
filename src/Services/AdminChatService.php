<?php

namespace Essasabbagh\LaravelChat\Services;

use Essasabbagh\LaravelChat\Contracts\PresenceDriver;
use Essasabbagh\LaravelChat\Models\Block;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Models\UserStatus;

class AdminChatService
{
    public function __construct(
        private PresenceDriver $presence,
    ) {}

    public function blockParticipant(string $blockerType, string|int $blockerId, string $blockedType, string|int $blockedId): Block
    {
        if (! config('chat.admin.allow_block', true)) {
            throw new \RuntimeException('Blocking is disabled');
        }

        return Block::firstOrCreate([
            'blocker_type' => $blockerType,
            'blocker_id' => $blockerId,
            'blocked_type' => $blockedType,
            'blocked_id' => $blockedId,
        ]);
    }

    public function unblockParticipant(string $blockerType, string|int $blockerId, string $blockedType, string|int $blockedId): void
    {
        if (! config('chat.admin.allow_block', true)) {
            throw new \RuntimeException('Blocking is disabled');
        }

        Block::where('blocker_type', $blockerType)
            ->where('blocker_id', $blockerId)
            ->where('blocked_type', $blockedType)
            ->where('blocked_id', $blockedId)
            ->delete();
    }

    public function forceOffline(string $userType, string|int $userId): void
    {
        if (! config('chat.admin.allow_force_offline', true)) {
            throw new \RuntimeException('Force offline is disabled');
        }

        $this->presence->offline($userId, $userType);
    }

    public function deleteMessage(Message $message): void
    {
        if (! config('chat.admin.allow_delete', true)) {
            throw new \RuntimeException('Message deletion is disabled');
        }

        $message->forceDelete();
    }

    public function deleteConversation(Conversation $conversation): void
    {
        if (! config('chat.admin.allow_delete', true)) {
            throw new \RuntimeException('Conversation deletion is disabled');
        }

        $conversation->forceDelete();
    }

    public function changeUserStatus(string $userType, string|int $userId, string $status): void
    {
        if (! config('chat.admin.allow_status_change', true)) {
            throw new \RuntimeException('Status change is disabled');
        }

        UserStatus::updateOrCreate(
            ['user_type' => $userType, 'user_id' => $userId],
            ['status' => $status, 'last_seen_at' => now()],
        );
    }
}
