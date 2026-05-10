<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'plate_number' => fake()->unique()->bothify('##?-####'),
            'vehicle_type' => fake()->randomElement(['Đầu kéo', 'Mooc', 'Xe tải']),
            'payload' => fake()->randomFloat(2, 1, 40),
            'registration_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'status' => fake()->randomElement(['available', 'busy', 'maintenance']),
        ];
    }
}
