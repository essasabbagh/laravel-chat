<?php

namespace Essasabbagh\LaravelChat\Events;

use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Essasabbagh\LaravelChat\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        $channel = app(TenantResolver::class)->resolve();

        $channelName = $channel
            ? "chat.{$channel}.conversation.{$this->message->conversation_id}"
            : "chat.conversation.{$this->message->conversation_id}";

        return [new Channel($channelName)];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_type' => $this->message->sender_type,
            'sender_id' => $this->message->sender_id,
            'body' => $this->message->body,
            'created_at' => $this->message->created_at,
        ];
    }
}
