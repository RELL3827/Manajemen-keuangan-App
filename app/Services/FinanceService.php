<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Transfer;

class FinanceService
{
    public function applyTransaction(Transaction $transaction): void
    {
        $account = $transaction->account;
        $amount = (float) $transaction->amount;

        if ($transaction->type === 'income') {
            $account->balance = (float) $account->balance + $amount;
        } else {
            $account->balance = (float) $account->balance - $amount;
        }

        $account->save();
    }

    public function reverseTransaction(Transaction $transaction): void
    {
        $account = $transaction->account;
        $amount = (float) $transaction->amount;

        if ($transaction->type === 'income') {
            $account->balance = (float) $account->balance - $amount;
        } else {
            $account->balance = (float) $account->balance + $amount;
        }

        $account->save();
    }

    public function transfer(Transfer $transfer): void
    {
        $from = $transfer->fromAccount;
        $to = $transfer->toAccount;

        $from->decrement('balance', $transfer->amount);
        $to->increment('balance', $transfer->amount);
    }

    public function reverseTransfer(Transfer $transfer): void
    {
        $from = $transfer->fromAccount;
        $to = $transfer->toAccount;

        $from->increment('balance', $transfer->amount);
        $to->decrement('balance', $transfer->amount);
    }
}