<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use App\Services\FinanceService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = $user->transactions()->with(['category', 'account', 'voiceNote']);

        $query = $this->applySmartSearch($query, $request->input('q'));

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->input('account_id'));
        }
        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }
        if ($request->filled('from') || $request->filled('to')) {
            $from = $request->filled('from') ? $request->input('from') : '1970-01-01';
            $to = $request->filled('to') ? $request->input('to') : now()->toDateString();
            $query->whereBetween('transaction_date', [$from, $to]);
        }

        $sort = $request->input('sort', 'date_desc');
        $query->when(in_array($sort, ['date_asc', 'date_desc', 'amount_asc', 'amount_desc', 'newest', 'oldest'], true), function ($q) use ($sort) {
            match ($sort) {
                'date_asc' => $q->orderBy('transaction_date')->orderBy('created_at'),
                'amount_asc' => $q->orderBy('amount'),
                'amount_desc' => $q->orderByDesc('amount'),
                'newest' => $q->orderByDesc('created_at'),
                'oldest' => $q->orderBy('created_at'),
                default => $q->orderByDesc('transaction_date')->orderByDesc('created_at'),
            };
        });

        $transactions = $query->paginate(15)->withQueryString();

        $categories = $user->categories()->get();
        $accounts = $user->accounts()->get();

        return view('transactions.index', compact('transactions', 'categories', 'accounts'));
    }

    private function applySmartSearch($query, ?string $q)
    {
        if (! $q) {
            return $query;
        }

        $raw = mb_strtolower(trim($q));
        $words = preg_split('/\s+/', $raw) ?: [];

        $from = null;
        $to = now()->toDateString();

        if (Str::contains($raw, 'bulan ini') && Str::contains($raw, 'kemarin')) {
            $from = now()->subMonth()->startOfMonth()->toDateString();
            $to = now()->startOfMonth()->subDay()->toDateString();
        } elseif (Str::contains($raw, 'bulan ini')) {
            $from = now()->startOfMonth()->toDateString();
        } elseif (Str::contains($raw, 'minggu ini')) {
            $from = now()->startOfWeek()->toDateString();
        } elseif (Str::contains($raw, 'tahun ini')) {
            $from = now()->startOfYear()->toDateString();
        } elseif (Str::contains($raw, 'hari ini')) {
            $from = now()->startOfDay()->toDateString();
        }

        if ($from) {
            $query->whereBetween('transaction_date', [$from, $to]);
        }

        if (Str::contains($raw, ['keluar', 'pengeluaran']) && ! Str::contains($raw, ['masuk', 'pemasukan'])) {
            $query->where('type', 'expense');
        } elseif (Str::contains($raw, ['masuk', 'pemasukan']) && ! Str::contains($raw, ['keluar', 'pengeluaran'])) {
            $query->where('type', 'income');
        }

        $ignore = ['pengeluaran', 'pemasukan', 'keluar', 'masuk', 'bulan', 'ini', 'minggu', 'tahun', 'kemarin', 'hari'];
        $filtered = array_values(array_filter($words, fn ($w) => ! in_array($w, $ignore, true)));

        if (count($filtered) > 0 || Str::contains($raw, 'makan')) {
            $categories = Auth::user()->categories()->pluck('id', 'name');

            $query->where(function ($q) use ($filtered, $categories, $raw) {
                $search = implode(' ', $filtered);
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhere('notes', 'like', '%' . $search . '%');

                foreach ($categories as $name => $id) {
                    if (count($filtered) > 0 && $this->matchesAny($filtered, $name)) {
                        $q->orWhere('category_id', $id);
                    }
                }
                if (Str::contains($raw, 'makan')) {
                    foreach (['Makanan'] as $name) {
                        if (isset($categories[$name])) {
                            $q->orWhere('category_id', $categories[$name]);
                        }
                    }
                }
            });
        }

        return $query;
    }

    private function matchesAny(array $words, string $subject): bool
    {
        return count(array_filter($words, fn ($w) => Str::contains(mb_strtolower($subject), $w) || Str::contains($w, mb_strtolower($subject)))) > 0;
    }

    public function show(Transaction $transaction): JsonResponse
    {
        $this->authorize('view', $transaction);

        $transaction->load(['category', 'account', 'voiceNote']);

        return response()->json([
            'transaction' => array_merge($transaction->toArray(), [
                'amount_format' => \App\Support\Money::format($transaction->amount),
                'date_format' => $transaction->transaction_date->translatedFormat('d F Y'),
                'type_label' => $transaction->type === 'income' ? 'Uang Masuk' : 'Uang Keluar',
                'source_label' => $transaction->source === 'voice' ? 'Voice' : 'Manual',
            ]),
        ]);
    }

    public function store(TransactionRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['source'] = $data['source'] ?? 'manual';

        $transaction = Transaction::create($data);

        (new FinanceService)->applyTransaction($transaction);
        if ($transaction->type === 'expense') {
            (new NotificationService)->checkBudgets(Auth::user(), $transaction->category_id);
        }

        $message = 'Transaksi berhasil disimpan.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'transaction' => $transaction->id]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(TransactionRequest $request, Transaction $transaction): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $transaction);

        (new FinanceService)->reverseTransaction($transaction);

        $data = $request->validated();
        $transaction->update($data);

        (new FinanceService)->applyTransaction($transaction);
        if ($transaction->type === 'expense') {
            (new NotificationService)->checkBudgets(Auth::user(), $transaction->category_id);
        }

        $message = 'Transaksi berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Transaction $transaction): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $transaction);

        (new FinanceService)->reverseTransaction($transaction);
        $transaction->delete();

        $message = 'Transaksi berhasil dihapus.';

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }
}