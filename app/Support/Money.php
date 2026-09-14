<?php

namespace App\Support;

class Money
{
    public static function format(float|int|null $amount, bool $prefix = true): string
    {
        $amount = (float) ($amount ?? 0);
        $formatted = number_format($amount, 0, ',', '.');

        return $prefix ? 'Rp' . $formatted : $formatted;
    }

    public static function short(?float $amount): string
    {
        $amount = (float) ($amount ?? 0);

        if ($amount >= 1000000000) {
            return 'Rp' . rtrim(rtrim(number_format($amount / 1000000000, 1, ',', ''), '0'), ',') . ' M';
        }

        if ($amount >= 1000000) {
            return 'Rp' . rtrim(rtrim(number_format($amount / 1000000, 1, ',', ''), '0'), ',') . ' jt';
        }

        if ($amount >= 1000) {
            return 'Rp' . rtrim(rtrim(number_format($amount / 1000, 1, ',', ''), '0'), ',') . ' rb';
        }

        return 'Rp' . number_format($amount, 0, ',', '.');
    }
}