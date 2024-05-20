<?php

namespace App\Http\Controllers;

use App\Models\Chatroom;
use App\Models\Chatmessage;
use Illuminate\Http\Request;
use App\Events\SendSeenEvent;
use App\Events\SendTypingEvent;
use App\Events\SendMessageEvent;
use App\Events\SendStopTypingEvent;
use Illuminate\Support\Facades\Auth;

class ChatmessageController extends Controller
{
    // Load more messages
    public function loadMessages(Request $request) {

        $id = $request->query('id');
        $loadedMessages = Chatmessage::where('chatroom_id', $id)
        ->orderBy("created_at", "desc")
        ->paginate(10);

        $loadedMessages = $loadedMessages->toArray();
        $finalData = array_reverse($loadedMessages['data']);

        return response()->json([
            "loadedMessages" => $finalData
        ], 200);
    }

    public function sendMessage(Request $request) {

        $message = Chatmessage::create($request->toArray());

        event(new SendMessageEvent($message));

        return response()->json([
            "newMessageFromServer" => $message
        ], 200);
    }

    public function sendImage(Request $req) {
        $userId = $req->userId;
        $chatroomId = $req->chatroomId;

        $req->validate([
            'image' => 'required', // adjust validation rules as needed
        ]);

        $fileName = uniqid() . $req->file("image") -> getClientOriginalName();
        $req->file('image')->storeAs('public/chatroomImages/',$fileName);

        $message = Chatmessage::create([
            'chatroom_id' => $chatroomId,
            'user_id' => $userId,
            'content' => '',
            'image' => $fileName,
        ]);
        event(new SendMessageEvent($message));

        return response()->json($fileName, 200);
    }

    public function updateSeen (Request $request) {

        $ids = $request['ids'];
        $data = $request['data'];

        Chatmessage::whereIn('id', $ids)->update([ 'seen' => true ]);
        event(new SendSeenEvent($data));

        return response()->json([
            'data' => $data
        ], 200);
    }

    public function isTyping (Request $request) {
        event(new SendTypingEvent($request->toArray()));
    }
    public function stopTyping (Request $request) {
        event(new SendStopTypingEvent($request->toArray()));
    }
}
