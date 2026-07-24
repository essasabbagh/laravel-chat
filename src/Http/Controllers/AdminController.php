<?php

namespace Essasabbagh\LaravelChat\Http\Controllers;

use Essasabbagh\LaravelChat\Models\Conversation;
use Essasabbagh\LaravelChat\Models\Message;
use Essasabbagh\LaravelChat\Services\AdminChatService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    public function __construct(
        private AdminChatService $adminService,
    ) {}

    public function block(Request $request)
    {
        $validated = $request->validate([
            'blocker_type' => 'required|string',
            'blocker_id' => 'required',
            'blocked_type' => 'required|string',
            'blocked_id' => 'required',
        ]);

        try {
            $block = $this->adminService->blockParticipant(
                $validated['blocker_type'],
                $validated['blocker_id'],
                $validated['blocked_type'],
                $validated['blocked_id'],
            );

            return response()->json($block, 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function unblock(Request $request)
    {
        $validated = $request->validate([
            'blocker_type' => 'required|string',
            'blocker_id' => 'required',
            'blocked_type' => 'required|string',
            'blocked_id' => 'required',
        ]);

        try {
            $this->adminService->unblockParticipant(
                $validated['blocker_type'],
                $validated['blocker_id'],
                $validated['blocked_type'],
                $validated['blocked_id'],
            );

            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function forceOffline(Request $request)
    {
        $validated = $request->validate([
            'user_type' => 'required|string',
            'user_id' => 'required',
        ]);

        try {
            $this->adminService->forceOffline(
                $validated['user_type'],
                $validated['user_id'],
            );

            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function deleteMessage(Conversation $conversation, Message $message)
    {
        try {
            $this->adminService->deleteMessage($message);

            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function deleteConversation(Conversation $conversation)
    {
        try {
            $this->adminService->deleteConversation($conversation);

            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    public function changeStatus(Request $request)
    {
        $validated = $request->validate([
            'user_type' => 'required|string',
            'user_id' => 'required',
            'status' => 'required|in:online,away,offline',
        ]);

        try {
            $this->adminService->changeUserStatus(
                $validated['user_type'],
                $validated['user_id'],
                $validated['status'],
            );

            return response()->json(null, 204);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
