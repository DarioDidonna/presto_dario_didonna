<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\PurchaseRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        $request = PurchaseRequest::factory()->create();

        return [
            'purchase_request_id' => $request->id,
            'user_id' => $request->buyer_id,
            'body' => fake()->sentence(),
        ];
    }
}
