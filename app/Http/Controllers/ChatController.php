<?php

namespace App\Http\Controllers;

use App\Events\ChatEvent;
use App\Models\Chat;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;

        $chatData = Chat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(
                'messages',
                'sender.customer',
                'sender.vet',
                'sender.petShop',
                'receiver.customer',
                'receiver.vet',
                'receiver.petShop',
            )
            ->latest()
            ->get();

        // Menggunakan koleksi Laravel untuk menyaring percakapan unik dengan satu lawan bicara
        $uniqueConversations = collect();

        foreach ($chatData as $chat) {
            // Membuat kunci unik berdasarkan peserta percakapan
            $key = $chat->sender_id == $userId
                ? $chat->receiver_id
                : $chat->sender_id;

            // Menambahkan percakapan ke koleksi jika belum ada, atau jika percakapan lebih baru
            if (!$uniqueConversations->has($key)) {
                $uniqueConversations->put($key, $chat);
            } else {
                $existingChat = $uniqueConversations->get($key);
                if ($chat->created_at > $existingChat->created_at) {
                    $uniqueConversations->put($key, $chat);
                }
            }
        }

        // Mengonversi koleksi kembali ke array
        $uniqueChatData = $uniqueConversations->values();

        return response()->json([
            'data' => $uniqueChatData
        ], 200);
    }

    public function indexRoomChat($id)
    {
        $user = Auth::user();
        $receiver_id = $id;

        // Mengambil semua percakapan dengan relasi yang diperlukan
        $conversations = Chat::with(
            'messages',
        )->get();

        // Memfilter percakapan yang melibatkan pengguna yang sedang login dan penerima
        $filteredConversations = $conversations->filter(function ($chat) use ($user, $receiver_id) {
            return ($chat->sender_id == $user->id && $chat->receiver_id == $receiver_id) ||
                ($chat->sender_id == $receiver_id && $chat->receiver_id == $user->id);
        });

        return response()->json([
            'message' => "Data messages",
            'data' => $filteredConversations->values(),
        ], 200);
    }


    public function sentChat(Request $request)
    {
        $user = Auth::user();
        $receiver = User::find($request->channelId);

        // if ($receiver->role == "Customer") {
        //     $receiver = User::with("customer")->find($id);
        // }
        // if ($receiver->role == "Vet") {
        //     $receiver = User::with("vet")->find($id);
        // }
        // if ($receiver->role == "Pet Shop") {
        //     $receiver = User::with("petShop")->find($id);
        // }
        if (!$user) {
            return response([
                'message' => 'Login required',
                'data' => null,
            ], 400);
        }

        $message = $user->messages()->create([
            'messages' => $request->messages
        ]);

        $conversation = Chat::create([
            'sender_id' => $message->user_id,
            'receiver_id' => $receiver->id,
            'messages_id' => $message->id,
        ]);

        broadcast(new ChatEvent($request->messages, "chat_$request->channelId"))->toOthers();
        return response([
            'message' => 'Message sended',
            'chat' => $message,
            'receiver' => $conversation,
        ], 200);
    }
}
