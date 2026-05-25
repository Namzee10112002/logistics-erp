<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'plate_number' => fake()->unique()->numerify('##').'B'.fake()->numberBetween(1, 9).'-'.fake()->numerify('###.##'),
            'vehicle_type' => fake()->randomElement(['Đầu kéo container', 'Mooc 40 feet', 'Xe tải 10 tấn', 'Xe tải lạnh']),
            'payload' => fake()->randomFloat(2, 1, 40),
            'registration_expiry' => fake()->dateTimeBetween('now', '+2 years'),
            'status' => fake()->randomElement(['available', 'busy', 'maintenance']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
