<?php

namespace Tests\Unit;

use App\Services\Rhid\RhidClient;
use App\Services\Rhid\RhidComplianceService;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

class RhidBankHourBalanceCanonicalizeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function canonicalize(array $row): array
    {
        $client = Mockery::mock(RhidClient::class);
        $svc = new RhidComplianceService($client);
        $m = new ReflectionMethod(RhidComplianceService::class, 'canonicalizeRhidBankHourBalanceFields');
        $m->setAccessible(true);

        return $m->invoke($svc, $row);
    }

    public function test_saldo_banco_horas_prevails_over_balance_and_saldo(): void
    {
        $out = $this->canonicalize([
            'name' => 'Test',
            'saldoBancoHoras' => -309,
            'balance' => 99999,
            'saldo' => 12345,
        ]);

        $this->assertSame(-309, $out['saldoBancoHoras']);
    }

    public function test_falls_back_to_balance_when_saldo_banco_horas_missing(): void
    {
        $out = $this->canonicalize([
            'name' => 'Test',
            'balance' => -100,
        ]);

        $this->assertSame(-100, $out['saldoBancoHoras']);
    }

    public function test_nested_person_overwrites_root_for_bank_fields(): void
    {
        $out = $this->canonicalize([
            'name' => 'Root',
            'saldoBancoHoras' => -500,
            'person' => [
                'strSaldoBancoHoras' => '02:59',
                'saldoBancoHoras' => 179,
            ],
        ]);

        $this->assertSame('02:59', $out['strSaldoBancoHoras']);
        $this->assertSame(179, $out['saldoBancoHoras']);
    }
}
