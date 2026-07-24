<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Events\ConversationUpdated;
use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ConversationsController extends Controller
{
    public function index(Request $request)
    {
        $participantType = $request->input('participant_type');
        $participantId = $request->input('participant_id');

        if (! $participantType || ! $participantId) {
            return response()->json(['message' => 'participant_type and participant_id are required'], 422);
        }

        $conversationIds = Participant::where('participantable_type', $participantType)
            ->where('participantable_id', $participantId)
            ->pluck('conversation_id');

        return Conversation::whereIn('id', $conversationIds)
            ->with('participants')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->cursorPaginate(20);
    }

    public function show(Conversation $conversation)
    {
        return $conversation->load('participants');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:direct,group',
            'name' => 'required_if:type,group|string|max:255',
            'created_by_type' => 'required_if:type,group|string',
            'created_by_id' => 'required_if:type,group',
        ]);

        if ($validated['type'] === 'group') {
            $whoCanCreate = config('chat.groups.who_can_create', 'any');
            if ($whoCanCreate === 'admin_role') {
                $isAdmin = Participant::where('participantable_type', $request->input('created_by_type'))
                    ->where('participantable_id', $request->input('created_by_id'))
                    ->where('role', 'admin')
                    ->exists();

                if (! $isAdmin) {
                    return response()->json(['message' => 'Only admins can create groups'], 403);
                }
            }
        }

        $conversation = Conversation::create($validated);

        if ($request->has('participants')) {
            foreach ($request->input('participants') as $participant) {
                $conversation->participants()->create([
                    'participantable_type' => $participant['type'],
                    'participantable_id' => $participant['id'],
                    'role' => $participant['role'] ?? 'member',
                    'joined_at' => now(),
                ]);
            }
        }

        ConversationUpdated::dispatch($conversation);

        return response()->json($conversation->load('participants'), 201);
    }

    public function destroy(Conversation $conversation)
    {
        $conversation->delete();

        return response()->json(null, 204);
    }
}
