<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $unread = $user->unread_notifications_count;

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
        $stats = $user->transactions()
            ->whereBetween('transaction_date', [$from, $to])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense,
                COUNT(*) as count
            ")
            ->first();

        $income = (float) ($stats->income ?? 0);
        $expense = (float) ($stats->expense ?? 0);
        $count = (int) ($stats->count ?? 0);
        $balance = (float) $user->accounts()->sum('balance');

        return [
            'balance' => $balance,
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'count' => $count,
            'format' => [
                'balance' => Money::format($balance),
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

        $startMonth = $carbonFrom->copy()->startOfMonth();
        $endMonth = $carbonTo->copy()->startOfMonth();
        $monthCount = $endMonth->diffInMonths($startMonth) + 1;

        if ($monthCount <= 3) {
            $rangeStart = $startMonth->copy();
            $rangeEnd = $endMonth->copy()->endOfMonth();
        } else {
            $yearStart = $carbonFrom->copy()->startOfYear();
            $rangeStart = $yearStart->copy();
            $rangeEnd = $yearStart->copy()->addMonths(11)->endOfMonth();
        }

        $driver = config('database.default');
        $dateFormat = $driver === 'sqlite' ? "strftime('%Y-%m', transaction_date)" : "TO_CHAR(transaction_date, 'YYYY-MM')";

        $rows = $user->transactions()
            ->whereBetween('transaction_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->selectRaw("{$dateFormat} as ym, type, SUM(amount) as total")
            ->groupBy(DB::raw("{$dateFormat}"), 'type')
            ->get();

        $lookup = [];
        foreach ($rows as $r) {
            $lookup[$r->ym][$r->type] = (float) $r->total;
        }

        $months = [];
        if ($monthCount <= 3) {
            $current = $startMonth->copy();
            while ($current->lte($endMonth)) {
                $ym = $current->format('Y-m');
                $months[] = [
                    'label' => $current->translatedFormat('M Y'),
                    'income' => $lookup[$ym]['income'] ?? 0.0,
                    'expense' => $lookup[$ym]['expense'] ?? 0.0,
                ];
                $current->addMonth();
            }
        } else {
            for ($i = 0; $i < 12; $i++) {
                $monthLabel = $rangeStart->copy()->addMonths($i);
                $ym = $monthLabel->format('Y-m');
                $months[] = [
                    'label' => $monthLabel->translatedFormat('M'),
                    'income' => $lookup[$ym]['income'] ?? 0.0,
                    'expense' => $lookup[$ym]['expense'] ?? 0.0,
                ];
            }
        }

        return $months;
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

        $afterDiff = (float) $user->transactions()
            ->where('transaction_date', '>', $to)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END), 0) as diff")
            ->value('diff');

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

        $rows = $user->transactions()
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("transaction_date, type, SUM(amount) as total")
            ->groupBy('transaction_date', 'type')
            ->get();

        $lookup = [];
        foreach ($rows as $r) {
            $dStr = is_string($r->transaction_date) ? substr($r->transaction_date, 0, 10) : $r->transaction_date->toDateString();
            $lookup[$dStr][$r->type] = (float) $r->total;
        }

        $labels = [];
        $income = [];
        $expense = [];

        for ($d = 0; $d < 7; $d++) {
            $day = $from->copy()->addDays($d);
            $dStr = $day->toDateString();
            $labels[] = $day->translatedFormat('D');

            $income[$d] = $lookup[$dStr]['income'] ?? 0.0;
            $expense[$d] = $lookup[$dStr]['expense'] ?? 0.0;
        }

        return compact('labels', 'income', 'expense');
    }
}