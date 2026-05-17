<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ShippingJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shipping_job_id' => ShippingJob::factory(),
            'dispatch_order_id' => null,
            'expense_type' => fake()->randomElement(['Phí nâng hạ', 'Phí cầu đường', 'Lưu bãi', 'Vệ sinh container']),
            'amount' => fake()->numberBetween(200000, 2000000),
            'note' => fake()->optional()->sentence(),
            'document_id' => null,
            'reported_by' => User::factory(),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
        ];
    }
}
