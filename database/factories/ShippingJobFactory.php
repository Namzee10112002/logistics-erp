<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingJob>
 */
class ShippingJobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'job_code' => 'JOB-'.now()->format('Ymd').'-'.fake()->unique()->numerify('###'),
            'customer_id' => Customer::factory(),
            'customs_declaration_no' => 'TK-'.fake()->unique()->numerify('##########'),
            'container_number' => strtoupper(fake()->bothify('????#######')),
            'pickup_location_id' => Location::factory(),
            'delivery_location_id' => Location::factory(),
            'cargo_type' => fake()->randomElement(['Hàng điện tử', 'May mặc', 'Nông sản', 'Máy móc', 'Hàng tiêu dùng']),
            'container_type' => fake()->randomElement(['20DC', '40HC', '40DC', 'LCL']),
            'expected_date' => fake()->dateTimeBetween('now', '+30 days'),
            'status' => fake()->randomElement(['new', 'processing', 'dispatched', 'completed', 'cancelled']),
            'created_by' => User::factory(),
        ];
    }
}
