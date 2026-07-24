<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Contracts\PresenceDriver;
use Essasabbagh\LaravelChat\Events\MessageStatusUpdated;
use Essasabbagh\LaravelChat\Events\UserPresenceChanged;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Models\MessageRead;
use Essasabbagh\LaravelChat\Tests\Models\TestAgent;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class PresenceAndReadsTest extends TestCase
{
    private TestCustomer $customer;

    private TestAgent $agent;

    private Conversation $conversation;

    private Message $message;

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

        $this->message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function can_mark_message_as_read()
    {
        Event::fake();

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$this->message->id}/read",
            [
                'participantable_type' => TestAgent::class,
                'participantable_id' => $this->agent->id,
            ]
        );

        $response->assertStatus(201);
        $this->assertDatabaseHas('chat_message_reads', [
            'message_id' => $this->message->id,
            'participantable_type' => TestAgent::class,
            'participantable_id' => $this->agent->id,
        ]);

        Event::assertDispatched(MessageStatusUpdated::class);
    }

    /** @test */
    public function double_read_does_not_duplicate()
    {
        MessageRead::create([
            'message_id' => $this->message->id,
            'participantable_type' => TestAgent::class,
            'participantable_id' => $this->agent->id,
            'read_at' => now(),
        ]);

        $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$this->message->id}/read",
            [
                'participantable_type' => TestAgent::class,
                'participantable_id' => $this->agent->id,
            ]
        );

        $this->assertDatabaseCount('chat_message_reads', 1);
    }

    /** @test */
    public function can_mark_all_as_read()
    {
        Message::factory()->count(3)->create([
            'conversation_id' => $this->conversation->id,
        ]);

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/read-all",
            [
                'participantable_type' => TestAgent::class,
                'participantable_id' => $this->agent->id,
            ]
        );

        $response->assertStatus(200);
        $this->assertEquals(4, $response->json('marked'));
    }

    /** @test */
    public function presence_driver_dispatches_events()
    {
        Event::fake();

        $driver = app(PresenceDriver::class);

        $driver->online('1', 'test_user');
        Event::assertDispatched(UserPresenceChanged::class, function ($event) {
            return $event->status === 'online';
        });

        $driver->away('1', 'test_user');
        Event::assertDispatched(UserPresenceChanged::class, function ($event) {
            return $event->status === 'away';
        });

        $driver->offline('1', 'test_user');
        Event::assertDispatched(UserPresenceChanged::class, function ($event) {
            return $event->status === 'offline';
        });
    }

    /** @test */
    public function presence_status_transitions()
    {
        $driver = app(PresenceDriver::class);

        $this->assertNull($driver->status('1', 'test_user'));

        $driver->online('1', 'test_user');
        $this->assertEquals('online', $driver->status('1', 'test_user'));

        $driver->away('1', 'test_user');
        $this->assertEquals('away', $driver->status('1', 'test_user'));

        $driver->offline('1', 'test_user');
        $this->assertEquals('offline', $driver->status('1', 'test_user'));
    }
}
