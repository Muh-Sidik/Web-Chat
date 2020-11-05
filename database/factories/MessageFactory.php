<?php

namespace Database\Factories;

use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        do {
            $from = rand(1, 10);
            $to = rand(1, 10);
            $status = rand(0, 1);
        } while ($from === $to);

        return [
            'from' => $from,
            'to' => $to,
            'body_message' => $this->faker->sentence(10),
            'status' => $status,
        ];
    }
}
