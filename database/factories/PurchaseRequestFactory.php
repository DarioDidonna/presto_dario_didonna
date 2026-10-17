<?php

namespace Database\Factories;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Richiesta senza oggetti: per i test che riguardano solo acquirente, venditore e stato
 * (il flusso completo passa da App\Services\Checkout).
 *
 * @extends Factory<PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'buyer_id' => User::factory(),
            'seller_id' => User::factory(),
            'status' => PurchaseRequest::PENDING,
            'message' => null,
            'total' => fake()->randomFloat(2, 5, 500),
        ];
    }

    public function status(string $status): static
    {
        return $this->state(['status' => $status]);
    }
}
