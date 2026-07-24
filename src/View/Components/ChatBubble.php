<?php

namespace Essasabbagh\LaravelChat\View\Components;

use Illuminate\View\Component;

class ChatBubble extends Component
{
    public function __construct(
        public string $body,
        public string $sender,
        public string $time,
        public bool $isMine = false,
    ) {}

    public function render()
    {
        return view('chat::components.chat-bubble');
    }
}
