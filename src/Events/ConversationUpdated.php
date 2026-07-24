<?php

namespace Essasabbagh\LaravelChat\Events;

use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Essasabbagh\LaravelChat\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Conversation $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation;
    }

    public function broadcastOn(): array
    {
        $tenant = app(TenantResolver::class)->resolve();

        $channelName = $tenant
            ? "chat.{$tenant}.conversation.{$this->conversation->id}"
            : "chat.conversation.{$this->conversation->id}";

        return [new Channel($channelName)];
    }
}
