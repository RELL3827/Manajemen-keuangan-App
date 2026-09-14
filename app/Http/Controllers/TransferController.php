<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Models\Transfer;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $transfers = $user->transfers()
            ->with(['fromAccount', 'toAccount'])
            ->orderByDesc('transfer_date')
            ->paginate(12);

        $accounts = $user->accounts()->get();

        return view('transfers.index', compact('transfers', 'accounts'));
    }

    public function store(TransferRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();

        $from = $user = Auth::user()->accounts()->findOrFail($data['from_account_id']);
        $to = Auth::user()->accounts()->findOrFail($data['to_account_id']);

        if ((float) $from->balance < (float) $data['amount']) {
            $message = 'Saldo ' . $from->name . ' tidak mencukupi.';

            return $request->expectsJson()
                ? response()->json(['ok' => false, 'message' => $message], 422)
                : redirect()->back()->with('error', $message);
        }

        $transfer = Transfer::create($data);
        (new FinanceService)->transfer($transfer);

        $message = 'Transfer berhasil dilakukan.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'transfer' => $transfer->id]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Transfer $transfer): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $transfer);

        (new FinanceService)->reverseTransfer($transfer);
        $transfer->delete();

        $message = 'Transfer berhasil dibatalkan.';

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }
}