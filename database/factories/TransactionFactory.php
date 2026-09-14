<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'type' => 'expense',
            'amount' => fake()->numberBetween(10000, 200000),
            'transaction_date' => fake()->date(),
            'payment_method' => 'Cash',
            'description' => null,
            'notes' => null,
            'source' => 'manual',
        ];
    }
}