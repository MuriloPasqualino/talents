<?php

namespace Tests\Unit;

use App\Services\Rhid\RhidComplianceService;
use Tests\TestCase;

class RhidJustificationListPayloadTest extends TestCase
{
    public function test_prepare_converts_yyyymmdd_ini_fim_to_iso_by_default(): void
    {
        config(['rhid.justification_list_ini_fim_format' => 'iso']);

        $service = app(RhidComplianceService::class);
        $prepared = $service->prepareJustificationListPayload([
            'ini' => '20260501',
            'fim' => '20260528',
            'page' => 0,
            'maxSize' => 500,
        ]);

        $this->assertSame('2026-05-01', $prepared['ini']);
        $this->assertSame('2026-05-28', $prepared['fim']);
        $this->assertIsArray($prepared['companies']);
    }
}
