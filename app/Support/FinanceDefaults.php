<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Category;
use App\Models\User;

class FinanceDefaults
{
    public static function createCategories(User $user): void
    {
        $income = [
            ['Gaji', 'bi-cash-coin', '#16a34a'],
            ['Bonus', 'bi-award', '#22c55e'],
            ['Bisnis', 'bi-briefcase', '#0ea5e9'],
            ['Freelance', 'bi-laptop', '#6366f1'],
            ['Investasi', 'bi-graph-up-arrow', '#8b5cf6'],
            ['Hadiah', 'bi-gift', '#f59e0b'],
            ['Lainnya', 'bi-three-dots', '#6b7280'],
        ];

        $expense = [
            ['Makanan', 'bi-cup-hot', '#f97316'],
            ['Transportasi', 'bi-fuel-pump', '#0ea5e9'],
            ['Belanja', 'bi-bag', '#ec4899'],
            ['Tagihan', 'bi-receipt', '#ef4444'],
            ['Hiburan', 'bi-film', '#8b5cf6'],
            ['Pendidikan', 'bi-book', '#14b8a6'],
            ['Kesehatan', 'bi-heart-pulse', '#f43f5e'],
            ['Investasi', 'bi-graph-up', '#0f766e'],
            ['Tabungan', 'bi-piggy-bank', '#10b981'],
            ['Kebutuhan Rumah', 'bi-house', '#64748b'],
            ['Lainnya', 'bi-three-dots', '#6b7280'],
        ];

        foreach (array_merge($income, $expense) as [$name, $icon, $color]) {
            Category::create([
                'user_id' => $user->id,
                'name' => $name,
                'type' => in_array($name, array_column($income, 0), true) ? 'income' : 'expense',
                'icon' => $icon,
                'color' => $color,
                'is_default' => true,
            ]);
        }
    }

    public static function createDefaultAccounts(User $user): void
    {
        $accounts = [
            ['Cash', 'cash', 0, '#16a34a', true],
            ['GoPay', 'ewallet', 0, '#22c55e', false],
            ['DANA', 'ewallet', 0, '#3b82f6', false],
        ];

        foreach ($accounts as [$name, $type, $balance, $color, $default]) {
            Account::create([
                'user_id' => $user->id,
                'name' => $name,
                'type' => $type,
                'initial_balance' => $balance,
                'balance' => $balance,
                'color' => $color,
                'is_default' => $default,
            ]);
        }
    }
}