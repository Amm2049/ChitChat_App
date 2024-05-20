<?php

namespace App\Models;

use App\Models\User;
use App\Models\Chatmessage;
use App\Models\UserChatroom;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chatroom extends Model
{
    use HasFactory;

    protected $table = "chatrooms";
    protected $fillable = ['nickname'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps()->withPivot('id');
    }

    public function messages()
    {
        return $this->hasMany(Chatmessage::class);
    }

    public function pivot_users()
    {
        return $this->belongsTo(UserChatroom::class);
    }
}
