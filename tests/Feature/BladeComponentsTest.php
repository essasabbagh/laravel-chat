<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Tests\TestCase;
use Essasabbagh\LaravelChat\View\Components\ChatBubble;
use Essasabbagh\LaravelChat\View\Components\ChatWindow;

class BladeComponentsTest extends TestCase
{
    /** @test */
    public function chat_bubble_component_renders()
    {
        $view = $this->component(ChatBubble::class, [
            'body' => 'Hello',
            'sender' => 'Alice',
            'time' => '12:00',
        ]);

        $view->assertSee('Hello');
        $view->assertSee('Alice');
    }

    /** @test */
    public function chat_window_component_renders()
    {
        $view = $this->component(ChatWindow::class, [
            'conversationId' => '1',
            'participantType' => 'App\Models\User',
            'participantId' => '1',
        ]);

        $view->assertSee('Type a message');
    }
}
