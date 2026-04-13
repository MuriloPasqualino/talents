<?php

namespace Tests\Unit;

use App\Support\RhidBankBalanceFormat;
use PHPUnit\Framework\TestCase;

class RhidBankBalanceFormatTest extends TestCase
{
    public function test_minus_1581_minutes_is_minus_26_21(): void
    {
        $this->assertSame('-26:21', RhidBankBalanceFormat::minutesToHhMm(-1581));
    }

    public function test_minus_365_minutes_is_minus_6_05_espelho_equivalent(): void
    {
        $this->assertSame('-6:05', RhidBankBalanceFormat::minutesToHhMm(-365));
    }

    public function test_hh_mm_parses_to_minutes(): void
    {
        $this->assertSame(-365, RhidBankBalanceFormat::hhMmToSignedMinutes('-06:05'));
        $this->assertSame(179, RhidBankBalanceFormat::hhMmToSignedMinutes('02:59'));
    }

    public function test_api_minus_1581_does_not_equal_espelho_minus_06_05_in_minutes(): void
    {
        $api = -1581;
        $espelho = RhidBankBalanceFormat::hhMmToSignedMinutes('-06:05');
        $this->assertNotSame($espelho, $api);
        $this->assertSame(-1216, $api - $espelho);
    }
}
