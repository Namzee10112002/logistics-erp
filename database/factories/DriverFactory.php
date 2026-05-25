<?php

namespace Database\Factories;

use App\Models\Driver;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Driver>
 */
class DriverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'driver_code' => 'TX-'.fake()->unique()->numerify('######'),
            'full_name' => fake()->name(),
            'phone' => '0'.fake()->numerify('#########'),
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-22 years'),
            'license_number' => 'GPLX-'.fake()->numerify('############'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'start_date' => fake()->dateTimeBetween('-5 years', '-1 month'),
            'rank' => fake()->randomElement(['Tài xế chính', 'Tài xế container', 'Tài xế đường dài', 'Tài xế dự phòng']),
            'contract_expiry' => fake()->dateTimeBetween('+3 months', '+2 years'),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
