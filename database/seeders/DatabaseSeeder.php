<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@eltrack.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'currency' => 'IDR',
            ]
        );

        $this->seedCategories($user);
        $this->seedAccounts($user);
        $this->seedTransactions($user);
        $this->seedBudgets($user);
        $this->seedTransfer($user);
        $this->seedNotifications($user);
    }

    private function seedCategories(User $user): void
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

        foreach ($income as [$name, $icon, $color]) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name, 'type' => 'income'],
                ['icon' => $icon, 'color' => $color, 'is_default' => true]
            );
        }

        foreach ($expense as [$name, $icon, $color]) {
            Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name, 'type' => 'expense'],
                ['icon' => $icon, 'color' => $color, 'is_default' => true]
            );
        }
    }

    private function seedAccounts(User $user): void
    {
        $accounts = [
            ['Cash', 'cash', 750000, '#16a34a'],
            ['BCA', 'bank', 2500000, '#2563eb'],
            ['GoPay', 'ewallet', 300000, '#22c55e'],
            ['SeaBank', 'bank', 1200000, '#0d9488'],
            ['Mandiri', 'bank', 1500000, '#dc2626'],
            ['DANA', 'ewallet', 200000, '#3b82f6'],
            ['ShopeePay', 'ewallet', 150000, '#f97316'],
        ];

        foreach ($accounts as $i => [$name, $type, $balance, $color]) {
            Account::firstOrCreate(
                ['user_id' => $user->id, 'name' => $name],
                [
                    'type' => $type,
                    'initial_balance' => $balance,
                    'balance' => $balance,
                    'color' => $color,
                    'is_default' => $i === 0,
                ]
            );
        }
    }

    private function seedTransactions(User $user): void
    {
        $accounts = $user->accounts()->get();
        $categories = $user->categories()->get();

        $income = $categories->where('type', 'income');
        $expense = $categories->where('type', 'expense');

        $now = now();
        $start = now()->subMonths(6)->startOfMonth();

        for ($m = $start->copy(); $m->lte($now); $m->addMonth()) {
            $month = $m->copy();
            $lastDay = $month->copy()->endOfMonth()->min($now);

            if (in_array($month->month, [3, 6, 9, 12])) {
                $this->tx($user, $accounts, $income, 'Gaji', $month->copy()->day(1)->min($lastDay), 5800000, 'BCA', 'Transfer, bank');
            } elseif (in_array($month->month, [4, 7, 10])) {
                $this->tx($user, $accounts, $income, 'Gaji', $month->copy()->day(1)->min($lastDay), 5200000, 'BCA', 'Transfer, bank');
            } else {
                $this->tx($user, $accounts, $income, 'Gaji', $month->copy()->day(1)->min($lastDay), 5400000, 'Mandiri', 'Transfer, bank');
            }

            if (in_array($month->month, [3, 7])) {
                $this->tx($user, $accounts, $income, 'Bonus', $month->copy()->day(20)->min($lastDay), 1500000, 'BCA', 'Bonus kinerja');
            }

            if (in_array($month->month, [4, 6, 8])) {
                $this->tx($user, $accounts, $income, 'Freelance', $month->copy()->day(15)->min($lastDay), 850000, 'GoPay', 'Design project');
            }

            if (in_array($month->month, [5])) {
                $this->tx($user, $accounts, $income, 'Bisnis', $month->copy()->day(22)->min($lastDay), 400000, 'DANA', 'Jualan online');
            }

            if (in_array($month->month, [9])) {
                $this->tx($user, $accounts, $income, 'Investasi', $month->copy()->day(10)->min($lastDay), 220000, 'SeaBank', 'Dividen saham');
            }

            $this->tx($user, $accounts, $expense, 'Tagihan', $month->copy()->day(5)->min($lastDay), rand(350000, 420000), 'BCA', 'Listrik & air');
            $this->tx($user, $accounts, $expense, 'Tagihan', $month->copy()->day(8)->min($lastDay), 300000, 'BCA', 'Internet rumah');
            $this->tx($user, $accounts, $expense, 'Tagihan', $month->copy()->day(12)->min($lastDay), 100000, 'Mandiri', 'Pulsa & kuota');

            if (in_array($month->month, [3, 6, 9])) {
                $this->tx($user, $accounts, $expense, 'Kebutuhan Rumah', $month->copy()->day(9)->min($lastDay), rand(350000, 550000), 'Mandiri', 'Belanja bulanan dapur');
            }

            $this->tx($user, $accounts, $expense, 'Tabungan', $month->copy()->day(25)->min($lastDay), 500000, 'SeaBank', 'Nabung rutin');

            $days = $month->copy()->day(min($lastDay->day, 28));
            $count = $lastDay->diffInDays($month->addDays(6)) ?: 20;

            for ($d = 0; $d <= $month->copy()->endOfMonth()->min($now)->day; $d++) {
                $day = $month->copy()->startOfMonth()->addDays($d);
                if ($day->gt($now)) {
                    break;
                }

                $food = $this->tx($user, $accounts, $expense, 'Makanan', $day->copy()->setTime(12, rand(0, 59)), rand(18000, 48000), ['Cash', 'GoPay'][rand(0, 1)], ['Makan siang', 'Makan malam', 'Kopi & camilan'][rand(0, 2)]);

                if (rand(1, 3) === 1) {
                    $this->tx($user, $accounts, $expense, 'Transportasi', $day->copy()->setTime(7, rand(0, 59)), rand(10000, 30000), 'GoPay', 'Ojek online / bensin');
                }

                if (rand(1, 4) === 1) {
                    $this->tx($user, $accounts, $expense, 'Makanan', $day->copy()->setTime(19, rand(0, 59)), rand(35000, 85000), ['BCA', 'ShopeePay'][rand(0, 1)], 'Makan bareng keluarga');
                }
            }

            foreach ([7, 14, 21, 28] as $d) {
                if ($month->copy()->day($d)->lte($now) && $month->copy()->day($d)->lte($lastDay)) {
                    $this->tx($user, $accounts, $expense, 'Belanja', $month->copy()->day($d)->setTime(16, rand(0, 59)), rand(60000, 190000), 'Mandiri', 'Belanja mingguan');
                }
            }

            for ($d = 6; $d <= $month->copy()->endOfMonth()->min($now)->day; $d += 7) {
                $day = $month->copy()->day($d);
                if ($day->gt($now) || $day->gt($lastDay)) {
                    continue;
                }
                if (rand(1, 2) === 1) {
                    $this->tx($user, $accounts, $expense, 'Hiburan', $day->copy()->setTime(20, rand(0, 59)), rand(50000, 150000), 'GoPay', 'Nonton / hangout');
                }
            }

            if (rand(1, 3) === 1) {
                $this->tx($user, $accounts, $expense, 'Kesehatan', $month->copy()->day(rand(18, 24))->min($lastDay)->setTime(9, rand(0, 59)), rand(40000, 120000), 'Cash', 'Vitamin & obat');
            }

            if (in_array($month->month, [4, 8])) {
                $this->tx($user, $accounts, $expense, 'Pendidikan', $month->copy()->day(16)->min($lastDay), rand(200000, 350000), 'BCA', 'Kursus online');
            }

            $this->tx($user, $accounts, $expense, 'Investasi', $month->copy()->day(26)->min($lastDay), 300000, 'SeaBank', 'Beli reksa dana');
        }
    }

    private function tx(User $user, $accounts, $categories, string $name, $date, int $amount, $accountOrMethod, ?string $description = null): Transaction
    {
        $category = $categories->firstWhere('name', $name);

        if (! $category || ! $date || $date->gt(now())) {
            return new Transaction();
        }

        $method = is_string($accountOrMethod) ? $accountOrMethod : 'Cash';
        $account = $accounts->random();

        if (is_string($accountOrMethod)) {
            $match = $accounts->firstWhere('name', $accountOrMethod);
            if ($match) {
                $account = $match;
            }
        }

        $type = $category->type;

        $tx = Transaction::create([
            'user_id' => $user->id,
            'account_id' => $account->id,
            'category_id' => $category->id,
            'type' => $type,
            'amount' => $amount,
            'transaction_date' => $date->toDateString(),
            'payment_method' => $method,
            'description' => $description,
            'notes' => null,
            'source' => rand(1, 6) === 1 ? 'voice' : 'manual',
        ]);

        $account->balance = $type === 'income'
            ? $account->balance + $amount
            : $account->balance - $amount;
        $account->save();

        return $tx;
    }

    private function seedBudgets(User $user): void
    {
        $categories = $user->categories()->where('type', 'expense')->get();
        $month = now()->startOfMonth();

        $budgets = [
            ['Makanan', 900000],
            ['Transportasi', 500000],
            ['Belanja', 800000],
            ['Tagihan', 1200000],
            ['Hiburan', 400000],
        ];

        foreach ($budgets as [$name, $amount]) {
            $category = $categories->firstWhere('name', $name);
            if ($category) {
                Budget::firstOrCreate(
                    ['user_id' => $user->id, 'category_id' => $category->id, 'period' => 'monthly', 'start_date' => $month->copy()->startOfMonth()->toDateString()],
                    ['amount' => $amount, 'end_date' => $month->copy()->endOfMonth()->toDateString()]
                );
            }
        }
    }

    private function seedTransfer(User $user): void
    {
        $cash = $user->accounts()->where('name', 'Cash')->first();
        $seabank = $user->accounts()->where('name', 'SeaBank')->first();

        if ($cash && $seabank) {
            Transfer::firstOrCreate(
                ['user_id' => $user->id, 'from_account_id' => $cash->id, 'to_account_id' => $seabank->id, 'transfer_date' => now()->subDays(3)->toDateString(), 'amount' => 200000],
                ['description' => 'Pindah saldo ke tabungan']
            );
            $cash->decrement('balance', 200000);
            $seabank->increment('balance', 200000);
        }
    }

    private function seedNotifications(User $user): void
    {
        UserNotification::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Selamat datang di Eltrack 🎉'],
            ['type' => 'system', 'message' => 'Kamu bisa langsung mencatat transaksi dengan suara, sentuh tombol mikrofon di halaman utama.', 'read_at' => null]
        );

        UserNotification::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Ringkasan mingguan'],
            ['type' => 'weekly', 'message' => 'Pengeluaran minggu ini Rp' . number_format(rand(800000, 1100000), 0, ',', '.') . ', pemasukan Rp' . number_format(rand(1300000, 2400000), 0, ',', '.') . '.', 'read_at' => null]
        );

        UserNotification::firstOrCreate(
            ['user_id' => $user->id, 'title' => 'Budget Makanan hampir habis'],
            ['type' => 'budget', 'message' => 'Pengeluaran makanan sudah mencapai 85% dari budget.', 'read_at' => null]
        );
    }
}