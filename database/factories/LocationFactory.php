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
            'location_code' => 'LOC-'.fake()->unique()->numerify('###'),
            'location_name' => fake()->city().' Port',
            'type' => fake()->randomElement(['port', 'depot', 'warehouse', 'factory', 'other']),
            'address' => fake()->address(),
            'province' => fake()->randomElement(['Hải Phòng', 'Quảng Ninh', 'Hải Dương', 'Hưng Yên', 'Thái Bình', 'Nam Định', 'Bắc Ninh', 'Hà Nội']),
            'status' => fake()->randomElement(['active', 'inactive', 'maintenance', 'overloaded']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
