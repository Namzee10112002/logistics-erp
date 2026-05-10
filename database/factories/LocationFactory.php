<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'location_name' => fake()->city().' Port',
            'type' => fake()->randomElement(['port', 'depot', 'warehouse', 'factory', 'other']),
            'address' => fake()->address(),
            'province' => fake()->state(),
        ];
    }
}
