<?php

namespace Database\Factories;

use App\Models\DispatchOrder;
use App\Models\Driver;
use App\Models\Location;
use App\Models\ShippingJob;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DispatchOrder>
 */
class DispatchOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['dispatched', 'on_way', 'completed']);

        return [
            'order_number' => 'DO-'.now()->format('ymd').'-'.fake()->unique()->numerify('###'),
            'shipping_job_id' => ShippingJob::factory(),
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
            'dispatch_status' => $status,
            'note' => fake()->optional()->sentence(),
            'start_location_id' => Location::factory(),
            'end_location_id' => Location::factory(),
            'loading_percent' => $status === 'completed' ? 100 : fake()->numberBetween(0, 80),
            'current_latitude' => fake()->latitude(8, 23),
            'current_longitude' => fake()->longitude(102, 110),
            'start_time' => fake()->dateTimeBetween('-3 days', 'now'),
            'end_time' => $status === 'completed' ? fake()->dateTimeBetween('-2 days', 'now') : null,
            'fuel_quota' => fake()->randomFloat(2, 20, 70),
            'toll_quota' => fake()->numberBetween(200000, 1200000),
            'created_by' => User::factory(),
        ];
    }
}
