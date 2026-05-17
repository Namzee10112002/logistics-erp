<?php

namespace Database\Factories;

use App\Models\ServicePrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServicePrice>
 */
class ServicePriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_code' => 'GOI-'.fake()->unique()->numerify('####'),
            'service_name' => fake()->randomElement(['Vận chuyển 20DC', 'Vận chuyển 40HC', 'Nâng hạ container', 'Lưu ca bãi']),
            'unit' => fake()->randomElement(['Chuyến', 'Cont', 'Ngày']),
            'unit_price' => fake()->numberBetween(300000, 5000000),
            'is_tax_included' => fake()->boolean(),
        ];
    }
}
