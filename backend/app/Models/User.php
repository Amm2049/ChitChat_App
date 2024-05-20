<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Chatroom;
use App\Models\Chatmessage;
use App\Models\UserChatroom;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = "users";
    protected $fillable = [
        'name',
        'email',
        'online',
        'profileUrl',
        'password',
    ];

    public function chatrooms()
    {
        return $this->belongsToMany(Chatroom::class)->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Chatmessage::class);
    }

    public function pivot_chatrooms()
    {
        return $this->belongsTo(UserChatroom::class);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
