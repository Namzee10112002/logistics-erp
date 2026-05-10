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
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'license_number' => 'GPLX-'.fake()->numerify('############'),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
