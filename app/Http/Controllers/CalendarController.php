<?php

namespace App\Http\Controllers;

use App\Support\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CalendarController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $month = $request->get('month', now()->format('Y-m'));
        $cursor = Carbon::parse($month . '-01');
        $start = $cursor->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $end = $cursor->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $transactions = $user->transactions()
            ->with('category')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->get(['transaction_date', 'type', 'amount', 'category_id', 'user_id']);

        $grouped = $transactions->groupBy(fn ($tx) => $tx->transaction_date->toDateString());

        $cells = [];
        $day = $start->copy();
        while ($day->lte($end)) {
            $key = $day->toDateString();
            $items = $grouped->get($key, collect());

            $income = (float) $items->where('type', 'income')->sum('amount');
            $expense = (float) $items->where('type', 'expense')->sum('amount');

            $cells[] = [
                'date' => $day->copy(),
                'is_today' => $key === now()->toDateString(),
                'is_outside' => $day->month !== $cursor->month,
                'day' => $day->day,
                'income' => $income,
                'income_format' => Money::short($income),
                'expense' => $expense,
                'expense_format' => Money::short($expense),
                'net' => $income - $expense,
                'net_format' => Money::format($income - $expense),
                'count' => $items->count(),
            ];

            $day->addDay();
        }

        $monthIncome = (float) $transactions->where('type', 'income')->sum('amount');
        $monthExpense = (float) $transactions->where('type', 'expense')->sum('amount');

        return view('calendar.index', [
            'cells' => $cells,
            'month' => $cursor,
            'prevMonth' => $cursor->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $cursor->copy()->addMonth()->format('Y-m'),
            'monthIncome' => $monthIncome,
            'monthExpense' => $monthExpense,
            'monthNet' => $monthIncome - $monthExpense,
        ]);
    }
}