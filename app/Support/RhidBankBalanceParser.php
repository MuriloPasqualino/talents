<?php

namespace App\Support;

/**
 * Leitura de saldo de banco de horas a partir de linhas da API RHID (espelha rhidDate.js).
 */
final class RhidBankBalanceParser
{
    /** @var list<string> */
    private const NUMERIC_KEYS = [
        'saldobancohoras', 'bancohoras', 'saldo', 'minutesbank', 'balance',
        'totalbancohoras', 'strbanco', 'strsaldo',
    ];

    /**
     * @param  array<string, mixed>  $row
     */
    public static function parseMinutesFromRow(?array $row): ?int
    {
        if ($row === null || $row === []) {
            return null;
        }

        $merged = self::mergeRow($row);

        $strRaw = isset($merged['strSaldoBancoHoras']) && is_scalar($merged['strSaldoBancoHoras'])
            ? trim((string) $merged['strSaldoBancoHoras'])
            : '';
        if ($strRaw !== '') {
            $parsed = RhidBankBalanceFormat::hhMmToSignedMinutes($strRaw);
            if ($parsed !== null) {
                return $parsed;
            }
            if (preg_match('/(\d+)\s*h/i', $strRaw, $hx) || preg_match('/(\d+)\s*min/i', $strRaw, $mx)) {
                $h = isset($hx[1]) ? (int) $hx[1] : 0;
                $m = isset($mx[1]) ? (int) $mx[1] : 0;
                $total = $h * 60 + $m;
                $neg = str_starts_with($strRaw, '-');

                return $neg ? -$total : $total;
            }
            if (is_numeric(str_replace(',', '.', $strRaw))) {
                return (int) round((float) str_replace(',', '.', $strRaw));
            }

            return null;
        }

        foreach (self::NUMERIC_KEYS as $lc) {
            $v = self::valueByLowerKey($merged, $lc);
            if ($v === null || $v === '') {
                continue;
            }
            if (is_numeric($v)) {
                return (int) round((float) $v);
            }

            return null;
        }

        if (isset($merged['saldoBancoHoras']) && is_numeric($merged['saldoBancoHoras'])) {
            return (int) round((float) $merged['saldoBancoHoras']);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private static function mergeRow(array $row): array
    {
        $merged = $row;
        $rootOnly = $row;
        foreach (['person', 'Person', 'pessoa', 'Pessoa'] as $nk) {
            unset($rootOnly[$nk]);
        }
        foreach ($rootOnly as $k => $v) {
            $merged[$k] = $v;
        }
        foreach (['person', 'Person'] as $nk) {
            if (isset($row[$nk]) && is_array($row[$nk])) {
                foreach ($row[$nk] as $k => $v) {
                    $merged[$k] = $v;
                }
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $arr
     */
    private static function valueByLowerKey(array $arr, string $lower): mixed
    {
        foreach ($arr as $k => $v) {
            if (strtolower((string) $k) === $lower) {
                return $v;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public static function displayName(array $row): string
    {
        $keys = ['strPersonName', 'personName', 'name', 'nome', 'strNome', 'strName'];
        foreach (['person', 'Person'] as $nk) {
            $nest = isset($row[$nk]) && is_array($row[$nk]) ? $row[$nk] : null;
            foreach ($keys as $k) {
                if ($nest !== null && isset($nest[$k]) && trim((string) $nest[$k]) !== '') {
                    return trim((string) $nest[$k]);
                }
            }
        }
        foreach ($keys as $k) {
            if (isset($row[$k]) && trim((string) $row[$k]) !== '') {
                return trim((string) $row[$k]);
            }
        }
        if (isset($row['idPerson'])) {
            return 'ID '.$row['idPerson'];
        }

        return '—';
    }
}
