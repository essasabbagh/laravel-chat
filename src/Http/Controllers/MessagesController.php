<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Events\MessageSent;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class MessagesController extends Controller
{
    public function index(Request $request, Conversation $conversation)
    {
        $messages = $conversation->messages()
            ->with(['sender', 'attachments', 'reactions'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate(50);

        return $messages;
    }

    public function show(Conversation $conversation, Message $message)
    {
        return $message->load(['sender', 'attachments', 'reactions', 'replyTo']);
    }

    public function store(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'sender_type' => 'required|string',
            'sender_id' => 'required',
            'reply_to_id' => 'nullable|exists:chat_messages,id',
        ]);

        $message = $conversation->messages()->create($validated);

        if ($request->filled('reply_to_id')) {
            $replyTo = Message::where('id', $request->input('reply_to_id'))->first();
            if ($replyTo !== null) {
                $message->update(['reply_snippet' => $replyTo->body]);
            }
        }

        MessageSent::dispatch($message);

        return response()->json($message->load('sender'), 201);
    }

    public function destroy(Conversation $conversation, Message $message)
    {
        $message->delete();

        return response()->json(null, 204);
    }
}
