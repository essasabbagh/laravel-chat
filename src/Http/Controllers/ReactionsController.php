<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReactionsController extends Controller
{
    public function store(Request $request, Conversation $conversation, Message $message)
    {
        $validated = $request->validate([
            'emoji' => 'required|string|max:10',
            'reactor_type' => 'required|string',
            'reactor_id' => 'required',
        ]);

        $existing = Reaction::where('message_id', $message->id)
            ->where('reactor_type', $validated['reactor_type'])
            ->where('reactor_id', $validated['reactor_id'])
            ->where('emoji', $validated['emoji'])
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(null, 204);
        }

        $reaction = $message->reactions()->create($validated);

        return response()->json($reaction, 201);
    }

    public function destroy(Conversation $conversation, Message $message, Reaction $reaction)
    {
        $reaction->delete();

        return response()->json(null, 204);
    }
}
