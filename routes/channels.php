<?php

use Essasabbagh\LaravelChat\Channels\ChatChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.conversation.{conversation}', ChatChannel::class);
Broadcast::channel('chat.{tenant}.conversation.{conversation}', ChatChannel::class);
