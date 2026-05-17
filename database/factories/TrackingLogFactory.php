<?php

namespace Database\Factories;

use App\Models\DispatchOrder;
use App\Models\TrackingLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrackingLog>
 */
class TrackingLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dispatch_order_id' => DispatchOrder::factory(),
            'status_update' => fake()->randomElement(['dispatched', 'on_way', 'completed']),
            'updated_by' => User::factory(),
        ];
    }
}
