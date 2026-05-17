<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->unique()->userName(),
            'full_name' => fake()->name(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role_id' => Role::query()->inRandomOrder()->first()?->id ?? Role::factory(),
            'status' => 1,
            'employee_code' => 'NV-'.fake()->unique()->numerify('######'),
            'position' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Kinh doanh', 'Chứng từ', 'Điều vận', 'Kế toán', 'Đội xe']),
            'joined_at' => fake()->dateTimeBetween('-4 years', '-1 month'),
            'theme_color' => fake()->randomElement(['#1a237e', '#0f766e', '#0369a1', '#854d0e', '#166534']),
            'is_dark_mode' => fake()->boolean(20),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
