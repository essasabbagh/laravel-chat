<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Models\Attachment;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AttachmentsController extends Controller
{
    public function __construct(
        private AttachmentService $attachmentService
    ) {}

    public function store(Request $request, Conversation $conversation, Message $message)
    {
        $validated = $request->validate([
            'file' => 'required_without:url|file',
            'url' => 'required_without:file|url',
            'type' => 'required|string|in:image,file,voice,link,location,video',
        ]);

        try {
            if ($request->hasFile('file')) {
                $attachment = $this->attachmentService->upload(
                    $message,
                    $request->file('file'),
                    $validated['type']
                );
            } else {
                $attachment = $this->attachmentService->uploadFromUrl(
                    $message,
                    $validated['url']
                );
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($attachment, 201);
    }

    public function destroy(Conversation $conversation, Message $message, Attachment $attachment)
    {
        $attachment->delete();

        return response()->json(null, 204);
    }
}
