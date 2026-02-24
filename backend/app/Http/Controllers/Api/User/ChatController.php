<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ChatController extends Controller
{
    public function getOrCreateChat(Request $request)
    {
        $user = auth()->user();

        $chat = Chat::firstOrCreate([
            'user_id' => $user->id,
            'order_id' => $request->order_id
        ]);

        return response()->json([
            'success' => true,
            'data' => $chat
        ]);
    }

    public function messages($chatId)
    {
        $messages = ChatMessage::with('sender:id,name')
            ->where('chat_id', $chatId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|exists:chats,id',
            'message' => 'required|string'
        ]);

        $msg = ChatMessage::create([
            'chat_id' => $request->chat_id,
            'sender_id' => auth()->id(),
            'message' => $request->message
        ]);

        return response()->json([
            'success' => true,
            'data' => $msg
        ]);
    }

}
