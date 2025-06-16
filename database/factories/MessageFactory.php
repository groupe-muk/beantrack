<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Make sure to include the User model

class MessageFactory extends Factory
{
    public function definition(): array
    {
        $sender = User::inRandomOrder()->first();
        $receiver = User::where('id', '!=', $sender?->id)->inRandomOrder()->first();
        return [
            'id' => null,
            'sender_id' => $sender?->id,
            'receiver_id' => $receiver?->id,
            'content' => $this->faker->sentence(),
            'created_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
