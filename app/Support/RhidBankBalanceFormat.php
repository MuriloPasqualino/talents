<?php

namespace App\Support;

/**
 * Formatação alinhada ao espelho RHID (horas totais : minutos, com sinal).
 * A API envia tipicamente minutos inteiros em saldoBancoHoras.
 */
final class RhidBankBalanceFormat
{
    public static function minutesToHhMm(int|float $totalMinutes): string
    {
        $n = (int) round((float) $totalMinutes);
        $sign = $n < 0 ? '-' : '';
        $abs = abs($n);
        $h = intdiv($abs, 60);
        $m = $abs % 60;

        return sprintf('%s%d:%02d', $sign, $h, $m);
    }

    /**
     * Converte string HH:mm com sinal opcional (ex.: "-06:05", "2:59") para minutos inteiros.
     */
    public static function hhMmToSignedMinutes(string $value): ?int
    {
        $t = trim($value);
        if ($t === '') {
            return null;
        }
        $neg = str_starts_with($t, '-');
        $t = ltrim($t, '-');
        if (! preg_match('/^(\d+):(\d{2})$/', $t, $m)) {
            return null;
        }
        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($min > 59) {
            return null;
        }
        $abs = $h * 60 + $min;

        return $neg ? -$abs : $abs;
    }
}
