<?php

namespace Database\Factories;

use App\Models\RecurringExpense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RecurringExpense>
 */
class RecurringExpenseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'expense_code' => 'FIX-'.fake()->unique()->numerify('####'),
            'name' => fake()->randomElement(['Lương khoán văn phòng', 'Thuê bãi cố định', 'Chi phí phần mềm', 'Bảo trì thiết bị']),
            'category' => fake()->randomElement(['Nhân sự', 'Văn phòng', 'Kho bãi', 'Hệ thống']),
            'amount' => fake()->numberBetween(2000000, 50000000),
            'cycle' => fake()->randomElement(['monthly', 'quarterly', 'yearly']),
            'effective_from' => fake()->dateTimeBetween('-6 months', 'now'),
            'effective_to' => null,
            'status' => fake()->randomElement(['active', 'inactive']),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
