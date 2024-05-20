<?php

namespace Database\Factories;

use App\Models\Chatroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chatroom>
 */
class ChatroomFactory extends Factory
{
    protected $model = Chatroom::class;

    public function definition()
    {
        return [
            'nickname' => $this->faker->word,
        ];
    }
}
