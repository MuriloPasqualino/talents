<?php

namespace App\Support;

/**
 * Normaliza telefone brasileiro para API ZapSign (phone_country + phone_number sem DDI).
 */
final class BrazilMobilePhone
{
    /**
     * @return array{country: string, national: string}|null
     */
    public static function parse(?string $input): ?array
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $input) ?? '';
        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '55')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) < 10 || strlen($digits) > 11) {
            return null;
        }

        $ddd = (int) substr($digits, 0, 2);
        if ($ddd < 11 || $ddd > 99) {
            return null;
        }

        return ['country' => '55', 'national' => $digits];
    }
}
