<?php

namespace Database\Factories;

use App\Models\PurchaseRequest;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'purchase_request_id' => PurchaseRequest::factory()->status(PurchaseRequest::ACCEPTED),
            'reviewer_id' => fn (array $attributes) => PurchaseRequest::find($attributes['purchase_request_id'])->buyer_id,
            'seller_id' => fn (array $attributes) => PurchaseRequest::find($attributes['purchase_request_id'])->seller_id,
            'rating' => fake()->numberBetween(1, Review::MAX_RATING),
            'comment' => fake()->optional()->sentence(),
        ];
    }
}
