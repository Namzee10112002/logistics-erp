<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_code' => 'KH-'.now()->format('ym').'-'.fake()->unique()->numerify('###'),
            'customer_name' => fake()->company(),
            'company_name' => fake()->company(),
            'tax_code' => fake()->unique()->numerify('##########'),
            'address' => fake()->address(),
            'contact_person' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
        ];
    }
}
