<?php

namespace App\Models;

use App\Models\User;
use App\Models\Chatroom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chatmessage extends Model
{
    use HasFactory;

    protected $table = "chatmessages";
    protected $fillable = ['chatroom_id', 'user_id', 'content', 'image'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function chatroom()
    {
        return $this->belongsTo(Chatroom::class);
    }

}
