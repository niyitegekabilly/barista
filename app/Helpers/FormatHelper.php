<?php

namespace App\Helpers;

class FormatHelper
{
    public static function formatRwf($amount)
    {
        return 'FRw ' . number_format($amount, 0, '.', ',');
    }

    public static function formatCurrency($amount, $currency = 'RWF')
    {
        if ($currency === 'RWF') {
            return 'FRw ' . number_format($amount, 0, '.', ',');
        }
        return number_format($amount, 2);
    }
}
