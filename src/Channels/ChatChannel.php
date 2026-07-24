<?php

namespace Essasabbagh\LaravelChat\Channels;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Participant;

class ChatChannel
{
    public function join($user, Conversation $conversation): bool
    {
        return Participant::where('conversation_id', $conversation->id)
            ->where('participantable_type', get_class($user))
            ->where('participantable_id', $user->getKey())
            ->exists();
    }
}
