<?php

namespace Database\Factories;

use App\Models\CashAdvance;
use App\Models\ShippingJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashAdvance>
 */
class CashAdvanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'approved', 'settled', 'rejected']);

        return [
            'shipping_job_id' => ShippingJob::factory(),
            'dispatch_order_id' => null,
            'requested_by' => User::factory(),
            'approved_by' => in_array($status, ['approved', 'settled'], true) ? User::factory() : null,
            'amount' => fake()->numberBetween(500000, 3000000),
            'reason' => fake()->sentence(),
            'status' => $status,
        ];
    }
}
