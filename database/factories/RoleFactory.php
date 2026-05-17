<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_code' => strtoupper(fake()->unique()->bothify('ROLE_???###')),
            'role_name' => fake()->jobTitle(),
            'description' => fake()->sentence(),
        ];
    }
}
