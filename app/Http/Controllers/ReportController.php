<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Transaction;
use App\Services\InsightService;
use App\Support\Money;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private function period(Request $request): array
    {
        $period = $request->get('period', 'month');

        [$from, $to] = match ($period) {
            'day' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'custom' => [
                Carbon::parse($request->get('from', now()->startOfMonth()->toDateString()))->startOfDay(),
                Carbon::parse($request->get('to', now()->toDateString()))->endOfDay(),
            ],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };

        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'from_carbon' => $from,
            'to_carbon' => $to,
            'period' => $period,
        ];
    }

    private function data(array $p, $user): array
    {
        $base = $user->transactions()->whereBetween('transaction_date', [$p['from'], $p['to']]);

        $income = (float) (clone $base)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $base)->where('type', 'expense')->sum('amount');

        $categoryOut = $this->categoryTotals($user, 'expense', $p);
        $categoryIn = $this->categoryTotals($user, 'income', $p);

        $monthlyStream = $this->monthlyStream($user, $p);

        return [
            'balance' => (float) $user->accounts()->sum('balance'),
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'count' => (clone $base)->count(),
            'category_expense' => $categoryOut,
            'category_income' => $categoryIn,
            'monthly_stream' => $monthlyStream,
            'transactions' => $user->transactions()->with(['category', 'account'])->whereBetween('transaction_date', [$p['from'], $p['to']])->orderByDesc('transaction_date')->limit(400)->get(),
        ];
    }

    private function categoryTotals($user, string $type, array $p): array
    {
        return $user->transactions()
            ->where('type', $type)
            ->whereBetween('transaction_date', [$p['from'], $p['to']])
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(fn ($rows) => [
                'name' => $rows->first()->category->name,
                'color' => $rows->first()->category->color,
                'total' => (float) $rows->sum('amount'),
                'total_format' => Money::format((float) $rows->sum('amount')),
            ])
            ->values()
            ->toArray();
    }

    private function monthlyStream($user, array $p): array
    {
        $start = Carbon::parse($p['from']);
        $end = Carbon::parse($p['to']);

        $isYearly = $start->diffInMonths($end) > 11;
        if ($isYearly) {
            $rangeStart = $start->copy()->startOfYear();
            $rangeEnd = $rangeStart->copy()->addMonths(11)->endOfMonth();
        } else {
            $rangeStart = $start->copy()->startOfMonth();
            $rangeEnd = $end->copy()->endOfMonth();
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

        $labels = [];
        $income = [];
        $expense = [];

        if ($isYearly) {
            for ($i = 0; $i < 12; $i++) {
                $m = $rangeStart->copy()->addMonths($i);
                $ym = $m->format('Y-m');
                $labels[] = $m->translatedFormat('M');
                $income[] = $lookup[$ym]['income'] ?? 0.0;
                $expense[] = $lookup[$ym]['expense'] ?? 0.0;
            }
        } else {
            $cursor = $rangeStart->copy();
            while ($cursor->lte($rangeEnd)) {
                $ym = $cursor->format('Y-m');
                $labels[] = $cursor->translatedFormat('M Y');
                $income[] = $lookup[$ym]['income'] ?? 0.0;
                $expense[] = $lookup[$ym]['expense'] ?? 0.0;
                $cursor->addMonth();
            }
        }

        return compact('labels', 'income', 'expense');
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $p = $this->period($request);
        $data = $this->data($p, $user);
        $insights = (new InsightService)->generate($user, $p['from_carbon'], $p['to_carbon']);

        return view('reports.index', [
            'p' => $p,
            'data' => $data,
            'insights' => $insights,
        ]);
    }

    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $p = $this->period($request);
        $data = $this->data($p, $user);

        $pdf = Pdf::loadView('reports.pdf', [
            'p' => $p,
            'data' => $data,
            'user' => $user,
            'title' => 'Laporan Keuangan ' . $p['period'] . ' ' . Carbon::parse($p['from'])->translatedFormat('d M Y') . ' s.d. ' . Carbon::parse($p['to'])->translatedFormat('d M Y'),
        ])->setPaper('a4');

        return $pdf->download('laporan-keuangan-' . $p['from'] . '-to-' . $p['to'] . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        $p = $this->period($request);
        $data = $this->data($p, $user);

        $report = new \App\Exports\ReportExport($data, $p);

        return Excel::download($report, 'laporan-keuangan-' . $p['from'] . '-to-' . $p['to'] . '.xlsx');
    }
}