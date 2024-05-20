<?php

namespace Database\Factories;

use App\Models\Chatmessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Chatmessage>
 */
class ChatmessageFactory extends Factory
{
    protected $model = Chatmessage::class;

    public function definition()
    {
        return [
            'content' => $this->faker->sentence,
        ];
    }
}
