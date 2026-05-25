<?php

namespace Database\Factories;

use App\Models\FieldAssignment;
use App\Models\FieldStaff;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FieldAssignment>
 */
class FieldAssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_code' => 'PHT-'.fake()->unique()->numerify('######'),
            'shipping_job_id' => ShippingJob::factory(),
            'field_staff_id' => FieldStaff::factory(),
            'location_id' => Location::factory(),
            'created_by' => User::factory(),
            'assigned_date' => now()->toDateString(),
            'tasks' => ['Kiểm tra cont'],
            'status' => 'assigned',
            'note' => fake()->optional()->sentence(),
            'assigned_at' => now(),
        ];
    }
}
