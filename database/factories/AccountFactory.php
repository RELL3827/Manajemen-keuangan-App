<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->randomElement(['Cash', 'BCA', 'GoPay', 'Mandiri', 'DANA']),
            'type' => fake()->randomElement(['cash', 'bank', 'ewallet', 'other']),
            'initial_balance' => 500000,
            'balance' => 500000,
            'icon' => null,
            'color' => '#6366f1',
            'is_default' => false,
        ];
    }
}