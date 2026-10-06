<?php

namespace App\Support;

/**
 * Money helpers. Amounts are always integers in the currency's minor unit,
 * as configured in config/kustore.php ("currencies").
 */
final class Money
{
    public static function format(?int $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?: config('kustore.currency', 'IDR'));
        $cfg = config("kustore.currencies.$currency", ['symbol' => $currency.' ', 'exponent' => 2, 'thousands' => ',', 'decimal' => '.']);
        $exp = (int) $cfg['exponent'];
        $value = ($amount ?? 0) / (10 ** $exp);

        return trim($cfg['symbol'].' '.number_format($value, $exp, $cfg['decimal'], $cfg['thousands']));
    }

    /** Convert user input ("150.000", "150000", "12.50") to minor units. */
    public static function parse(string|int|float|null $input, ?string $currency = null): ?int
    {
        if ($input === null || $input === '') {
            return null;
        }
        $currency = strtoupper($currency ?: config('kustore.currency', 'IDR'));
        $exp = (int) config("kustore.currencies.$currency.exponent", 2);

        if (is_string($input)) {
            if ($exp === 0) {
                $input = preg_replace('/[^\d]/', '', $input);
            } else {
                $input = str_replace(',', '', $input);
            }
        }

        return (int) round(((float) $input) * (10 ** $exp));
    }

    /** Minor units to a plain input value for forms. */
    public static function toInput(?int $amount, ?string $currency = null): string
    {
        if ($amount === null) {
            return '';
        }
        $currency = strtoupper($currency ?: config('kustore.currency', 'IDR'));
        $exp = (int) config("kustore.currencies.$currency.exponent", 2);

        return $exp === 0 ? (string) $amount : number_format($amount / (10 ** $exp), $exp, '.', '');
    }
}
