<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AdminChatController extends Controller
{

    public function rooms()
    {
        $rooms = Chat::with(['user:id,name'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    public function messages($id)
    {
        $messages = ChatMessage::with('sender:id,name')
            ->where('chat_id', $id)
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
            'message' => $request->message,
        ]);

        return response()->json([
            'success' => true,
            'data' => $msg
        ]);
    }
}
