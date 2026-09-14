<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $today = now()->toDateString();
        $recent = $user->transactions()
            ->with(['category', 'account', 'voiceNote'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $expenseCategories = $user->categories()->where('type', 'expense')->limit(3)->get();
        $budgets = $user->budgets()->with('category')->get();
        $totalBudget = $budgets->sum('amount');
        $totalSpent = $budgets->sum(fn ($b) => $b->spentAmount());
        $unread = $user->notificationsUser()->unread()->count();

        [$from, $to] = [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()];
        $summary = $this->summary($user, $from, $to);

        return view('dashboard.index', compact('recent', 'expenseCategories', 'budgets', 'totalBudget', 'totalSpent', 'unread', 'summary'));
    }

    public function data(Request $request): JsonResponse
    {
        $user = Auth::user();

        [$from, $to] = $this->resolvePeriod($request);

        $summary = $this->summary($user, $from, $to);
        $trend = $this->trend($user, $from, $to);
        $categoryExpense = $this->categoryBreakdown($user, 'expense', $from, $to);
        $categoryIncome = $this->categoryBreakdown($user, 'income', $from, $to);
        $balanceTrend = $this->balanceTrend($user, $from, $to);
        $last7 = $this->transactions7($user);

        return response()->json(compact('summary', 'trend', 'categoryExpense', 'categoryIncome', 'balanceTrend', 'last7'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'month');
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay()->toDateString(), $now->copy()->endOfDay()->toDateString()],
            'week' => [$now->copy()->startOfWeek()->toDateString(), $now->copy()->endOfWeek()->toDateString()],
            'quarter' => [$now->copy()->startOfQuarter()->toDateString(), $now->copy()->endOfQuarter()->toDateString()],
            'year' => [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()],
            'custom' => [$request->get('from', $now->copy()->startOfMonth()->toDateString()), $request->get('to', $now->toDateString())],
            default => [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()],
        };
    }

    private function summary($user, string $from, string $to): array
    {
        $tx = $user->transactions()->whereBetween('transaction_date', [$from, $to]);

        $income = (float) (clone $tx)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $tx)->where('type', 'expense')->sum('amount');
        $count = (clone $tx)->count();

        return [
            'balance' => (float) $user->accounts()->sum('balance'),
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'count' => $count,
            'format' => [
                'balance' => Money::format($user->accounts()->sum('balance')),
                'income' => Money::format($income),
                'expense' => Money::format($expense),
                'net' => Money::format($income - $expense),
            ],
        ];
    }

    private function trend($user, string $from, string $to): array
    {
        $carbonFrom = Carbon::parse($from);
        $carbonTo = Carbon::parse($to);
        $months = [];

        $startMonth = $carbonFrom->copy()->startOfMonth();
        $endMonth = $carbonTo->copy()->startOfMonth();
        $monthCount = $endMonth->diffInMonths($startMonth) + 1;

        if ($monthCount <= 3) {
            $current = $startMonth->copy();
            while ($current->lte($endMonth)) {
                $label = $current->translatedFormat('M Y');
                [$income, $expense] = $this->monthTotals($user, $current);
                $months[] = ['label' => $label, 'income' => $income, 'expense' => $expense];
                $current->addMonth();
            }

            return $months;
        }

        $yearStart = $carbonFrom->copy()->startOfYear();
        for ($i = 0; $i < 12; $i++) {
            $monthLabel = $yearStart->copy()->addMonths($i);
            [$income, $expense] = $this->monthTotals($user, $monthLabel);
            $months[] = ['label' => $monthLabel->translatedFormat('M'), 'income' => $income, 'expense' => $expense];
        }

        return $months;
    }

    private function monthTotals($user, Carbon $month): array
    {
        $q = $user->transactions()->whereBetween('transaction_date', [
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString(),
        ]);

        return [
            (float) (clone $q)->where('type', 'income')->sum('amount'),
            (float) (clone $q)->where('type', 'expense')->sum('amount'),
        ];
    }

    private function categoryBreakdown($user, string $type, string $from, string $to): array
    {
        $rows = $user->transactions()
            ->where('type', $type)
            ->whereBetween('transaction_date', [$from, $to])
            ->with('category')
            ->get()
            ->groupBy('category_id');

        $out = [];
        foreach ($rows as $items) {
            $first = $items->first();
            $out[] = [
                'name' => $first->category->name,
                'color' => $first->category->color,
                'icon' => $first->category->icon,
                'total' => (float) $items->sum('amount'),
                'total_format' => Money::format((float) $items->sum('amount')),
            ];
        }

        usort($out, fn ($a, $b) => $b['total'] <=> $a['total']);

        return $out;
    }

    private function balanceTrend($user, string $from, string $to): array
    {
        $todayBalance = (float) $user->accounts()->sum('balance');

        $periodTransactions = $user->transactions()
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->get(['transaction_date', 'type', 'amount']);

        $periodDiff = 0;
        foreach ($periodTransactions as $tx) {
            $periodDiff += $tx->type === 'income' ? (float) $tx->amount : -((float) $tx->amount);
        }

        $after = $user->transactions()->where('transaction_date', '>', $to)->get(['type', 'amount']);
        $afterDiff = 0;
        foreach ($after as $tx) {
            $afterDiff += $tx->type === 'income' ? (float) $tx->amount : -((float) $tx->amount);
        }

        $running = $todayBalance - $afterDiff - $periodDiff;

        $balances = [];
        foreach ($periodTransactions as $tx) {
            $running += $tx->type === 'income' ? (float) $tx->amount : -((float) $tx->amount);
            $key = $tx->transaction_date->translatedFormat('d M');
            $balances[$key] = round($running);
        }

        return [
            'labels' => array_keys($balances),
            'values' => array_values($balances),
        ];
    }

    private function transactions7($user): array
    {
        $from = now()->subDays(6)->startOfDay();
        $to = now()->endOfDay();

        $labels = [];
        $income = [];
        $expense = [];

        for ($d = 0; $d < 7; $d++) {
            $day = $from->copy()->addDays($d);
            $labels[] = $day->translatedFormat('D');

            $income[$d] = (float) $user->transactions()->where('type', 'income')->whereDate('transaction_date', $day)->sum('amount');
            $expense[$d] = (float) $user->transactions()->where('type', 'expense')->whereDate('transaction_date', $day)->sum('amount');
        }

        return compact('labels', 'income', 'expense');
    }
}