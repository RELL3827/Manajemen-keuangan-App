<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserNotification;

class NotificationService
{
    public function checkBudgets(User $user, ?int $categoryId = null): void
    {
        if (! $user->notif_budget) {
            return;
        }

        $budgets = $user->budgets()->with('category')
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->get();

        foreach ($budgets as $budget) {
            $spent = $budget->spentAmount();
            $total = (float) $budget->amount;

            if ($total <= 0) {
                continue;
            }

            $ratio = $spent / $total;

            if ($ratio >= 1) {
                $this->ensure([
                    'user_id' => $user->id,
                    'type' => 'budget',
                    'title' => "Budget {$budget->category->name} telah terlampaui",
                    'message' => "Pengeluaran {$budget->category->name} mencapai Rp" . number_format($spent, 0, ',', '.') . " dan melebihi budget Rp" . number_format($total, 0, ',', '.') . '.',
                ], now()->toDateString());
            } elseif ($ratio >= 0.9) {
                $this->ensure([
                    'user_id' => $user->id,
                    'type' => 'budget',
                    'title' => "Budget {$budget->category->name} hampir habis",
                    'message' => 'Pengeluaran ' . $budget->category->name . ' sudah mencapai ' . (int) ($ratio * 100) . '% dari budget.',
                ], now()->toDateString());
            }
        }
    }

    public function ensureWeeklySummary(User $user): void
    {
        if (! $user->notif_weekly) {
            return;
        }

        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $income = (float) $user->transactions()->where('type', 'income')->whereBetween('transaction_date', [$start, $end])->sum('amount');
        $expense = (float) $user->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$start, $end])->sum('amount');

        $this->ensure([
            'user_id' => $user->id,
            'type' => 'weekly',
            'title' => 'Ringkasan keuangan mingguan',
            'message' => 'Minggu ini: masuk Rp' . number_format($income, 0, ',', '.') . ', keluar Rp' . number_format($expense, 0, ',', '.') . '.'
                . ($income >= $expense ? ' Kinerja keuangan kamu sehat. 💪' : ' Yuk jaga pengeluaran tetap terkontrol.'),
        ], now()->toDateString());
    }

    public function ensureMonthlySummary(User $user): void
    {
        if (! $user->notif_monthly) {
            return;
        }

        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $income = (float) $user->transactions()->where('type', 'income')->whereBetween('transaction_date', [$start, $end])->sum('amount');
        $expense = (float) $user->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$start, $end])->sum('amount');

        $this->ensure([
            'user_id' => $user->id,
            'type' => 'monthly',
            'title' => 'Ringkasan keuangan bulanan',
            'message' => 'Bulan ini: masuk Rp' . number_format($income, 0, ',', '.') . ', keluar Rp' . number_format($expense, 0, ',', '.') . ', selisih Rp' . number_format($income - $expense, 0, ',', '.') . '.',
        ], now()->toDateString());
    }

    public function ensureReminder(User $user): void
    {
        if (! $user->notif_reminder) {
            return;
        }

        $hasToday = $user->transactions()->whereDate('transaction_date', now())->exists();

        if ($hasToday) {
            return;
        }

        $this->ensure([
            'user_id' => $user->id,
            'type' => 'reminder',
            'title' => 'Ada transaksi hari ini?',
            'message' => 'Catat uang masuk dan keluar kamu hari ini biar laporan tetap akurat. Cukup tekan tombol mikrofon.',
        ], now()->toDateString());
    }

    public function ensure(array $data, string $uniqueKey): void
    {
        $exists = UserNotification::where('user_id', $data['user_id'])
            ->where('type', $data['type'])
            ->whereDate('created_at', $uniqueKey)
            ->where('title', $data['title'])
            ->exists();

        if (! $exists) {
            UserNotification::create($data);
        }
    }
}