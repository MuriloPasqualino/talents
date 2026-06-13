<?php

namespace Tests\Unit;

use App\Services\Commercial\CommercialProposalServiceLines;
use App\Support\CommercialProposalPdfDefaults;
use PHPUnit\Framework\TestCase;

class CommercialProposalServiceLinesTest extends TestCase
{
    public function test_resolve_description_uses_override_first(): void
    {
        $defaults = CommercialProposalPdfDefaults::defaultServiceDescriptions();

        $result = CommercialProposalServiceLines::resolveDescription(
            'pesquisas',
            ['pesquisas' => 'Texto customizado'],
            $defaults,
        );

        $this->assertSame('Texto customizado', $result);
    }

    public function test_resolve_description_falls_back_to_default(): void
    {
        $defaults = CommercialProposalPdfDefaults::defaultServiceDescriptions();

        $result = CommercialProposalServiceLines::resolveDescription(
            'pesquisas',
            [],
            $defaults,
        );

        $this->assertStringContainsString('O que contempla:', $result);
    }

    public function test_resolve_description_uses_catalog_fallback(): void
    {
        $result = CommercialProposalServiceLines::resolveDescription(
            'produto-custom',
            [],
            [],
            'Descrição do catálogo',
        );

        $this->assertSame('Descrição do catálogo', $result);
    }
}
