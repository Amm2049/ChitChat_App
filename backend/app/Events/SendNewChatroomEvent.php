<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendNewChatroomEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $newChatroomData;

    public function __construct($newChatroomData)
    {
        $this->newChatroomData = $newChatroomData;
    }

    public function broadcastOn()
    {
        return new Channel('chatroom');
    }

    public function broadcastAs()
    {
        return 'newChatroom';
    }
}
