<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Events\MessageStatusUpdated;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Models\MessageRead;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReadsController extends Controller
{
    public function store(Request $request, Conversation $conversation, Message $message)
    {
        $validated = $request->validate([
            'participantable_type' => 'required|string',
            'participantable_id' => 'required',
        ]);

        $read = MessageRead::firstOrCreate(
            [
                'message_id' => $message->id,
                'participantable_type' => $validated['participantable_type'],
                'participantable_id' => $validated['participantable_id'],
            ],
            ['read_at' => now()]
        );

        if ($read->wasRecentlyCreated) {
            MessageStatusUpdated::dispatch(
                $message,
                'seen',
                $validated['participantable_type'],
                $validated['participantable_id']
            );
        }

        return response()->json($read, 201);
    }

    public function markAllRead(Request $request, Conversation $conversation)
    {
        $validated = $request->validate([
            'participantable_type' => 'required|string',
            'participantable_id' => 'required',
        ]);

        $unreadMessages = $conversation->messages()
            ->whereDoesntHave('reads', function ($q) use ($validated) {
                $q->where('participantable_type', $validated['participantable_type'])
                    ->where('participantable_id', $validated['participantable_id']);
            })
            ->get();

        foreach ($unreadMessages as $message) {
            $message->reads()->create([
                'participantable_type' => $validated['participantable_type'],
                'participantable_id' => $validated['participantable_id'],
                'read_at' => now(),
            ]);

            MessageStatusUpdated::dispatch(
                $message,
                'seen',
                $validated['participantable_type'],
                $validated['participantable_id']
            );
        }

        return response()->json(['marked' => $unreadMessages->count()]);
    }
}
