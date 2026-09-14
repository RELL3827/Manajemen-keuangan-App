<?php

namespace App\Services;

use App\Models\User;
use App\Support\Money;
use Carbon\Carbon;

class InsightService
{
    public function generate(User $user, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->startOfMonth();
        $to = $to ?? now();

        $insights = [];
        $currency = $user->currency ?? 'IDR';

        $income = (float) $user->transactions()->where('type', 'income')->whereBetween('transaction_date', [$from, $to])->sum('amount');
        $expense = (float) $user->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$from, $to])->sum('amount');

        $topExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$from, $to])
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => [
                'name' => $rows->first()->category->name,
                'total' => $rows->sum('amount'),
            ])
            ->sortByDesc('total')
            ->first();

        if ($topExpense && $topExpense['total'] > 0) {
            $insights[] = [
                'icon' => 'bi-fire',
                'color' => 'var(--ft-red)',
                'text' => "Pengeluaran terbesar kamu periode ini adalah {$topExpense['name']}, sebesar " . Money::format($topExpense['total']) . '.',
            ];
        }

        $prevFrom = (clone $from)->subMonths(1);
        $prevTo = (clone $from)->subDay();
        if ($prevTo->gte($prevFrom)) {
            $prevExpense = (float) $user->transactions()->where('type', 'expense')->whereBetween('transaction_date', [$prevFrom, $prevTo])->sum('amount');

            if ($prevExpense > 0 && $topExpense && $topExpense['total'] > 0) {
                $growth = (($expense - $prevExpense) / $prevExpense) * 100;
                $arrow = $growth >= 0 ? 'naik' : 'turun';
                $insights[] = [
                    'icon' => $growth >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right',
                    'color' => $growth >= 0 ? 'var(--ft-red)' : 'var(--ft-green)',
                    'text' => 'Pengeluaran total ' . $arrow . ' ' . number_format(abs($growth), 0) . '% dibanding periode sebelumnya (Rp' . number_format($prevExpense, 0, ',', '.') . ').',
                ];
            }
        }

        $days = max(1, $from->diffInDays($to) + 1);
        $avgDaily = $expense / $days;
        $insights[] = [
            'icon' => 'bi-graph-up-arrow',
            'color' => 'var(--ft-blue)',
            'text' => 'Rata-rata pengeluaran harian kamu ' . Money::format($avgDaily) . '.',
        ];

        $daysPassed = max(1, min(now()->diffInDays($from->copy()->startOfDay()) === 0 ? 1 : now()->diffInDays($from->copy()->startOfDay()) + 1, $days));
        $projected = ($expense / $daysPassed) * max(1, $from->daysInMonth);
        $insights[] = [
            'icon' => 'bi-calculator',
            'color' => 'var(--ft-amber)',
            'text' => 'Jika pola ini berlanjut, estimasi pengeluaran bulan ini berkisar ' . Money::format($projected) . '.',
        ];

        if ($income > $expense) {
            $insights[] = [
                'icon' => 'bi-shield-check',
                'color' => 'var(--ft-green)',
                'text' => 'Cash flow kamu positif: selisih pemasukan dan pengeluaran ' . Money::format($income - $expense) . '. Pertahankan!',
            ];
        }

        $savingsRate = $income > 0 ? (($income - $expense) / $income) * 100 : 0;
        if ($savingsRate < 10) {
            $insights[] = [
                'icon' => 'bi-exclamation-triangle',
                'color' => 'var(--ft-red)',
                'text' => 'Tingkat tabungan kamu rendah. Usahakan sisihkan minimal 10% dari pemasukan.',
            ];
        } else {
            $insights[] = [
                'icon' => 'bi-piggy-bank',
                'color' => 'var(--ft-green)',
                'text' => "Kamu berhasil menabung {$savingsRate}% dari total pemasukan periode ini. Bagus! 🎉",
            ];
        }

        return $insights;
    }
}