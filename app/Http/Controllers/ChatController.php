<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Services\GroqChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function sendMessage(Request $request, GroqChatService $chatService)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:2|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Pesan tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        if (!$chatService->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'AI chat belum dikonfigurasi.',
            ], 503);
        }

        $sessionId = $request->session()->getId();
        $userId = auth()->check() ? auth()->id() : null;

        ChatMessage::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'role' => 'user',
            'message' => $request->message,
            'is_ai' => false,
        ]);

        $context = null;
        if (auth()->check()) {
            $user = auth()->user();
            $context = "Pengguna yang login: {$user->name} (email: {$user->email}). Role: {$user->role}.";
        }

        $aiReply = $chatService->chat($request->message, $context);

        ChatMessage::create([
            'session_id' => $sessionId,
            'user_id' => $userId,
            'role' => 'assistant',
            'message' => $aiReply,
            'is_ai' => true,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'message' => $aiReply,
                'created_at' => now()->toISOString(),
            ],
        ]);
    }

    public function getHistory(Request $request)
    {
        $sessionId = $request->session()->getId();
        $userId = auth()->id();

        if ($userId) {
            $messages = ChatMessage::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();
        } else {
            $messages = ChatMessage::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get()
                ->reverse()
                ->values();
        }

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }
}
