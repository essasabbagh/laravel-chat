<?php

namespace Essasabbagh\LaravelChat\Tests\Feature;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Tests\Models\TestCustomer;
use Essasabbagh\LaravelChat\Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class AttachmentsTest extends TestCase
{
    private TestCustomer $customer;

    private Conversation $conversation;

    private Message $message;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = TestCustomer::create(['name' => 'Alice']);

        $this->conversation = Conversation::factory()->create();
        $this->conversation->participants()->create([
            'participantable_type' => TestCustomer::class,
            'participantable_id' => $this->customer->id,
            'role' => 'member',
        ]);

        $this->message = Message::factory()->create([
            'conversation_id' => $this->conversation->id,
            'sender_type' => TestCustomer::class,
            'sender_id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function can_upload_file_attachment()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$this->message->id}/attachments",
            [
                'file' => $file,
                'type' => 'image',
            ]
        );

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'type', 'path']);

        Storage::disk('local')->assertExists($response->json('path'));
    }

    /** @test */
    public function rejects_oversized_file()
    {
        $file = UploadedFile::fake()->create('large.pdf', 15000);

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$this->message->id}/attachments",
            [
                'file' => $file,
                'type' => 'file',
            ]
        );

        $response->assertStatus(422);
    }

    /** @test */
    public function can_attach_url_as_link()
    {
        Http::fake([
            'example.com/*' => Http::response(
                '<html><head><meta property="og:title" content="Test Page" /></head></html>'
            ),
        ]);

        $response = $this->postJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$this->message->id}/attachments",
            [
                'url' => 'https://example.com/test',
                'type' => 'link',
            ]
        );

        $response->assertStatus(201)
            ->assertJsonPath('type', 'link');
    }

    /** @test */
    public function can_delete_attachment()
    {
        $attachment = $this->message->attachments()->create([
            'type' => 'image',
            'path' => 'attachments/test.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        $response = $this->deleteJson(
            "/api/chat/conversations/{$this->conversation->id}/messages/{$this->message->id}/attachments/{$attachment->id}"
        );

        $response->assertStatus(204);
        $this->assertDatabaseMissing('chat_attachments', ['id' => $attachment->id]);
    }
}
