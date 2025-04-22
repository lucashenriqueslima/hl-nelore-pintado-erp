<?php

namespace App\Helpers;


class NumberHelper
{
    public static function formatToCurrency(float $value, bool $usePrefix = false): string
    {
        $value = number_format($value, 2, ',', '.');
        return $usePrefix ? "R$ $value" : $value;
    }

    public static function formatToRaw(string $value): float
    {
        return (float) str_replace(['.', ','], ['', '.'], $value);
    }
}
