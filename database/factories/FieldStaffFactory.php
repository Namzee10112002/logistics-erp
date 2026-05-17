<?php

namespace Database\Factories;

use App\Models\FieldStaff;
use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FieldStaff>
 */
class FieldStaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'staff_code' => 'HT-'.fake()->unique()->numerify('######'),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'certificates' => fake()->randomElement(['Chứng chỉ an toàn kho bãi', 'Chứng chỉ nghiệp vụ hải quan', 'Chứng chỉ PCCC cơ bản']),
            'responsible_location_id' => Location::factory()->state(['type' => 'warehouse']),
            'start_date' => fake()->dateTimeBetween('-3 years', '-1 month'),
            'status' => fake()->randomElement(['active', 'inactive']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
