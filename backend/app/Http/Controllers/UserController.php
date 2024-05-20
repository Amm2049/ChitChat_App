<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Chatroom;
use App\Models\Chatmessage;
use App\Models\UserChatroom;
use Illuminate\Http\Request;
use App\Events\SendUserOnlineEvent;
use App\Events\SendUserOfflineEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    // User Data
    public function getUserData (Request $request) {

        // Current user
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

            $messagesFromDb = Chatmessage::where("chatroom_id", $chatroom->chatroom_id)->get();
            $chatroom["messages"] = $messagesFromDb;

            return $chatroom;
        }, $chatrooms);
;
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

        // Friends
        $allFriends = User::where("id", "!=", $user->id)->get();

        // Pagination for chatrooms
        $currentPage = $chatrooms->toArray()['current_page'];
        $lastPage = $chatrooms->toArray()['last_page'];
        $total = $chatrooms->toArray()['total'];
        $paginationForChatrooms = [
            'currentPage' => $currentPage,
            'lastPage' => $lastPage,
            'total' => $total,
        ];

        return response()->json([
            "user" => $user,
            "chatrooms" => $chatroomsData,
            "messages" => $messages,
            "allFriends" => $allFriends,
            "paginationForChatrooms" => $paginationForChatrooms
        ], 200);
    }

    // Update user data
    public function createUserProfile (Request $req) {

        $userId = Auth::id();
        $image = User::find($userId)->profileUrl;

        if (File::exists(public_path() . '/storage/profile_images/' . $image) && is_file(public_path() . '/storage/profile_images/' . $image)) {
            File::delete(public_path() . '/storage/profile_images/' . $image);
        }

        $req->validate([
            'image' => 'required', // adjust validation rules as needed
        ]);

        $file = $req->file("image");
        $fileName = uniqid() . $req->file("image") -> getClientOriginalName();
        $req->file('image')->storeAs('public/profile_images/',$fileName);

        User::where('id', $userId)->update(['profileUrl' => $fileName]);
        return response()->json($fileName, 200);
    }

    // Set user online
    public function setUserOnline(Request $request) {
        $userId = Auth::user()->id;
        User::where('id', $userId)->update([ 'online' => true ]);
        event(new SendUserOnlineEvent($userId));
    }
    // Set user offline
    public function setUserOffline(Request $request) {
        $userId = Auth::user()->id;
        User::where('id', $userId)->update([ 'online' => false ]);
        event(new SendUserOfflineEvent($userId));
    }
}
