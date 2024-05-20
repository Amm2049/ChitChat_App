<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Chatroom;
use App\Models\Chatmessage;
use App\Models\UserChatroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\SendNewChatroomEvent;
use Illuminate\Support\Facades\Auth;

class ChatroomController extends Controller
{
    // Create chatroom
    public function createChatroom (Request $request) {

        $chatroom = Chatroom::create();

        // Pivot table
        $partner_id = $request->partnerId;
        $user_id = Auth::id();
        $data = [
            [ 'user_id' => $user_id, 'chatroom_id' => $chatroom->id, 'created_at' => now(), 'updated_at' => now() ],
            [ 'user_id' => $partner_id, 'chatroom_id' => $chatroom->id, 'created_at' => now(), 'updated_at' => now() ],
        ];
        $result = DB::table('chatroom_user')->insert($data);

        // Updated records
        $user = Auth::user();
        $chatroomsFromDb = $user->chatrooms;

        // Chatrooms
        $chatroomIds = [];
        foreach ($chatroomsFromDb as $chatroom) {
            $pivotData = $chatroom->pivot;
            $chatroomIds[] = $pivotData->chatroom_id;
        }

        $chatrooms = UserChatroom::whereIn("chatroom_id", $chatroomIds)
        ->where("user_id", "!=", $user->id)
        ->orderBy("created_at", "desc")
        ->paginate(8);

        $chatroomsData = $chatrooms->map(function ($chatroom){
            $partner = User::where("id", $chatroom->user_id)->first();
            $chatroom["partner"] = $partner;

            $chatroomDetail = Chatroom::where("id", $chatroom->chatroom_id)->first();
            $chatroom["chatroomName"] = $chatroomDetail->nickname;

            return $chatroom;
        }, $chatrooms);

        // Messages
        $messages = [];
        foreach($chatroomIds as $id){
            $data = Chatmessage::where('chatroom_id', $id)
            ->orderBy("created_at", "desc")
            ->paginate(15);
            $newData = $data->toArray();
            $finalData = [
                "data" => array_reverse($newData['data']),
                "currentPage" => $newData['current_page'],
                "lastPage" => $newData['last_page'],
                "total" => $newData['total'],
                "chatroom_id" => $id,
            ];
            $messages[] = $finalData;
        }

        // Pagination for chatroom
        $currentPage = $chatrooms->toArray()['current_page'];
        $lastPage = $chatrooms->toArray()['last_page'];
        $total = $chatrooms->toArray()['total'];
        $paginationForChatrooms = [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'total' => $total,
        ];

        event(new SendNewChatroomEvent([
            'createrId' => $user_id,
            'chatrooms' => $chatroomsData,
            'newChatroom' => $chatroom,
            'messages' => $messages,
            "paginationForChatrooms" => $paginationForChatrooms
        ]));

        return response()->json([
            'chatrooms' => $chatroomsData,
            'newChatroom' => $chatroom,
            'messages' => $messages,
            "paginationForChatrooms" => $paginationForChatrooms
        ], 200);
    }

    public function loadChatrooms (Request $request) {
        $user = Auth::user();
        $chatroomsFromDb = $user->chatrooms;

        // Chatrooms
        $chatroomIds = [];
        foreach ($chatroomsFromDb as $chatroom) {
            $pivotData = $chatroom->pivot;
            $chatroomIds[] = $pivotData->chatroom_id;
        }

        $chatrooms = UserChatroom::whereIn("chatroom_id", $chatroomIds)
        ->where("user_id", "!=", $user->id)
        ->orderBy("created_at", "desc")
        ->paginate(8);

        $chatroomsData = $chatrooms->map(function ($chatroom){
            $partner = User::where("id", $chatroom->user_id)->first();
            $chatroom["partner"] = $partner;

            $chatroomDetail = Chatroom::where("id", $chatroom->chatroom_id)->first();
            $chatroom["chatroomName"] = $chatroomDetail->nickname;

            return $chatroom;
        }, $chatrooms);

        $newChatroomIds = [];
        foreach($chatroomsData as $chatroom) {
            $newChatroomIds[] = $chatroom->chatroom_id;
        }

        // Messages
        $messages = [];
        foreach($newChatroomIds as $id){
            $data = Chatmessage::where('chatroom_id', $id)
            ->orderBy("created_at", "desc")
            ->paginate(15);
            $newData = $data->toArray();
            $finalData = [
                "data" => array_reverse($newData['data']),
                "currentPage" => $newData['current_page'],
                "lastPage" => $newData['last_page'],
                "total" => $newData['total'],
                "chatroom_id" => $id,
            ];
            $messages[] = $finalData;
        }

        return response()->json([
            'loadedChatrooms' => $chatroomsData,
            'loadedMessages' => $messages,
        ], 200);
    }
}
