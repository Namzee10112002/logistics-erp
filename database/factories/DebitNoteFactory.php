<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\ShippingJob;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DebitNote>
 */
class DebitNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $serviceFee = fake()->numberBetween(1500000, 5000000);
        $expensePaid = fake()->numberBetween(0, 2000000);

        return [
            'note_number' => 'DN-'.now()->format('ymd').'-'.fake()->unique()->numerify('###'),
            'shipping_job_id' => ShippingJob::factory(),
            'customer_id' => Customer::factory(),
            'total_service_fee' => $serviceFee,
            'total_expense_paid' => $expensePaid,
            'grand_total' => $serviceFee + $expensePaid,
            'issued_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'status' => fake()->randomElement(['unpaid', 'partial', 'paid']),
        ];
    }
}
