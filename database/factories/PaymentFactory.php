<?php

namespace Database\Factories;

use App\Models\DebitNote;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'debit_note_id' => DebitNote::factory(),
            'amount_paid' => fake()->numberBetween(500000, 5000000),
            'payment_method' => fake()->randomElement(['Chuyển khoản', 'Tiền mặt', 'Cấn trừ nợ']),
            'payment_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'received_by' => User::factory(),
            'reference_no' => strtoupper(fake()->bothify('REF-########')),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
