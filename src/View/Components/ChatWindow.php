<?php

namespace Essasabbagh\LaravelChat\View\Components;

use Illuminate\View\Component;

class ChatWindow extends Component
{
    public function __construct(
        public string $conversationId = '',
        public string $participantType = '',
        public string $participantId = '',
        public int $height = 500,
    ) {}

    public function render()
    {
        return view('chat::components.chat-window');
    }
}
