<?php

namespace Essasabbagh\LaravelChat\Events;

use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPresenceChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $userType,
        public string|int $userId,
        public string $status,
    ) {}

    public function broadcastOn(): array
    {
        $tenant = app(TenantResolver::class)->resolve();

        $channelName = $tenant
            ? "chat.{$tenant}.presence"
            : 'chat.presence';

        return [new Channel($channelName)];
    }

    public function broadcastWith(): array
    {
        return [
            'user_type' => $this->userType,
            'user_id' => (string) $this->userId,
            'status' => $this->status,
        ];
    }
}
