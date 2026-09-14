<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountRequest;
use App\Models\Account;
use App\Models\Transfer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $accounts = $user->accounts()->withCount('transactions')->get();
        $totalBalance = (float) $accounts->sum('balance');

        return view('accounts.index', compact('accounts', 'totalBalance'));
    }

    public function editInfo(Account $account): JsonResponse
    {
        $this->authorize('update', $account);

        return response()->json([
            'ok' => true,
            'account' => $account->only(['id', 'name', 'type', 'initial_balance', 'color', 'is_default']),
        ]);
    }

    public function store(AccountRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['balance'] = $data['initial_balance'];

        if ($request->boolean('is_default')) {
            Auth::user()->accounts()->update(['is_default' => false]);
        }

        $account = Account::create($data);

        $message = 'Akun berhasil ditambahkan.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message, 'account' => $account]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function update(AccountRequest $request, Account $account): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $account);

        $data = $request->validated();
        $delta = $data['initial_balance'] - (float) $account->initial_balance;
        $account->update($data);
        $account->balance = (float) $account->balance + $delta;
        $account->save();

        if ($request->boolean('is_default')) {
            Auth::user()->accounts()->where('id', '!=', $account->id)->update(['is_default' => false]);
        }

        $message = 'Akun berhasil diperbarui.';

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Account $account): JsonResponse|RedirectResponse
    {
        $this->authorize('delete', $account);

        if (Transfer::where('user_id', Auth::id())->where(function ($q) use ($account) {
            $q->where('from_account_id', $account->id)->orWhere('to_account_id', $account->id);
        })->exists()) {
            return $this->abortWith('Akun tidak dapat dihapus karena masih dipakai untuk transfer.', $account);
        }

        if (Transaction::where('user_id', Auth::id())->where('account_id', $account->id)->exists()) {
            return $this->abortWith('Akun tidak dapat dihapus karena memiliki riwayat transaksi.', $account);
        }

        $account->delete();

        $message = 'Akun berhasil dihapus.';

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return redirect()->back()->with('success', $message);
    }

    private function abortWith(string $message, $account): JsonResponse|RedirectResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['ok' => false, 'message' => $message], 422);
        }

        return redirect()->back()->with('error', $message);
    }
}