<?php

namespace Essasabbagh\LaravelChat\Events;

use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Essasabbagh\LaravelChat\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public string $status,
        public string $participantableType,
        public string|int $participantableId,
    ) {}

    public function broadcastOn(): array
    {
        $tenant = app(TenantResolver::class)->resolve();

        $channelName = $tenant
            ? "chat.{$tenant}.conversation.{$this->message->conversation_id}"
            : "chat.conversation.{$this->message->conversation_id}";

        return [new Channel($channelName)];
    }

    public function broadcastWith(): array
    {
        return [
            'message_id' => $this->message->id,
            'status' => $this->status,
            'participantable_type' => $this->participantableType,
            'participantable_id' => (string) $this->participantableId,
        ];
    }
}
