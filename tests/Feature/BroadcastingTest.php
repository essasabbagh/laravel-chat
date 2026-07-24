<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Contracts\TenantResolver;
use Essasabbagh\LaravelChat\Events\ConversationUpdated;
use Essasabbagh\LaravelChat\Events\MessageSent;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class BroadcastingTest extends TestCase
{
    private TestCustomer $customer;

    private TestAgent $agent;

    private Conversation $conversation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = TestCustomer::create(['name' => 'Alice']);
        $this->agent = TestAgent::create(['name' => 'Bob']);

        $this->conversation = Conversation::factory()->create();
        $this->conversation->participants()->createMany([
            ['participantable_type' => TestCustomer::class, 'participantable_id' => $this->customer->id, 'role' => 'member'],
            ['participantable_type' => TestAgent::class, 'participantable_id' => $this->agent->id, 'role' => 'member'],
        ]);
    }

    /** @test */
    public function message_sent_event_dispatched_on_store()
    {
        Event::fake();

        $this->postJson("/api/chat/conversations/{$this->conversation->id}/messages", [
            'body' => 'Test broadcast',
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);

        Event::assertDispatched(MessageSent::class, function ($event) {
            return $event->message->body === 'Test broadcast';
        });
    }

    /** @test */
    public function conversation_updated_event_dispatched_on_store()
    {
        Event::fake();

        $this->postJson('/api/chat/conversations', [
            'type' => 'direct',
            'participants' => [
                ['type' => TestCustomer::class, 'id' => $this->customer->id],
                ['type' => TestAgent::class, 'id' => $this->agent->id],
            ],
        ]);

        Event::assertDispatched(ConversationUpdated::class);
    }

    /** @test */
    public function message_sent_broadcasts_on_correct_channel()
    {
        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);

        $event = new MessageSent($message);
        $channels = $event->broadcastOn();

        $this->assertStringContainsString("conversation.{$this->conversation->id}", $channels[0]->name);
    }

    /** @test */
    public function tenant_channel_includes_tenant_prefix()
    {
        $resolver = new class implements TenantResolver
        {
            public function resolve(): string|int|null
            {
                return 'tenant_xyz';
            }
        };
        app()->instance(TenantResolver::class, $resolver);

        $message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $event = new MessageSent($message);
        $channels = $event->broadcastOn();

        $this->assertStringContainsString('chat.tenant_xyz.conversation', $channels[0]->name);
    }
}
