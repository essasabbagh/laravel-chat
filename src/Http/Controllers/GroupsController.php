<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GroupsController extends Controller
{
    public function addMember(Request $request, Conversation $conversation)
    {
        if ($conversation->type !== 'group') {
            return response()->json(['message' => 'Not a group conversation'], 422);
        }

        $validated = $request->validate([
            'participantable_type' => 'required|string',
            'participantable_id' => 'required',
            'role' => 'sometimes|in:member,admin',
        ]);

        $existing = $conversation->participants()
            ->where('participantable_type', $validated['participantable_type'])
            ->where('participantable_id', $validated['participantable_id'])
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Already a member'], 409);
        }

        $participant = $conversation->participants()->create([
            'participantable_type' => $validated['participantable_type'],
            'participantable_id' => $validated['participantable_id'],
            'role' => $validated['role'] ?? 'member',
            'joined_at' => now(),
        ]);

        return response()->json($participant, 201);
    }

    public function removeMember(Conversation $conversation, Participant $participant)
    {
        if ($conversation->type !== 'group') {
            return response()->json(['message' => 'Not a group conversation'], 422);
        }

        $participant->delete();

        return response()->json(null, 204);
    }

    public function updateRole(Request $request, Conversation $conversation, Participant $participant)
    {
        if ($conversation->type !== 'group') {
            return response()->json(['message' => 'Not a group conversation'], 422);
        }

        $validated = $request->validate([
            'role' => 'required|in:member,admin',
        ]);

        $participant->update(['role' => $validated['role']]);

        return response()->json($participant);
    }
}
