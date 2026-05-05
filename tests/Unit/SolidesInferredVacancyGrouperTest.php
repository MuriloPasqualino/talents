<?php

namespace Tests\Unit;

use App\Services\Solides\SolidesInferredVacancyGrouper;
use PHPUnit\Framework\TestCase;

class SolidesInferredVacancyGrouperTest extends TestCase
{
    public function test_infer_key_prefers_origin_over_seniority(): void
    {
        $this->assertSame(
            'Banco de talentos',
            SolidesInferredVacancyGrouper::inferKey('Banco de talentos', 'Trainee')
        );
    }

    public function test_infer_key_falls_back_to_seniority(): void
    {
        $this->assertSame('Trainee', SolidesInferredVacancyGrouper::inferKey(null, 'Trainee'));
    }

    public function test_merge_and_dedupe_curriculo_wins_over_passaporte_same_email(): void
    {
        $curriculos = [[
            'id' => 10,
            'fullName' => 'Maria',
            'mainEmail' => 'm@example.com',
            'idNumber' => '123',
            'origin' => 'Jobs',
            'seniority' => 'Operacional',
        ]];
        $passaportes = [[
            'id' => 99,
            'name' => 'Maria Pass',
            'email' => 'm@example.com',
            'idNumber' => '999',
            'profile' => 'EC',
        ]];

        $merged = SolidesInferredVacancyGrouper::mergeAndDedupe($curriculos, $passaportes);

        $this->assertCount(1, $merged);
        $this->assertSame('curriculo', $merged[0]['source']);
        $this->assertSame('Jobs', $merged[0]['inferred_key']);
    }

    public function test_build_grouped_catalog_filters_by_group_contains(): void
    {
        $candidates = [
            SolidesInferredVacancyGrouper::normalizeCurriculoRow([
                'id' => 1,
                'fullName' => 'A',
                'mainEmail' => 'a@x.com',
                'origin' => 'Alpha Jobs',
                'seniority' => '',
            ]),
            SolidesInferredVacancyGrouper::normalizeCurriculoRow([
                'id' => 2,
                'fullName' => 'B',
                'mainEmail' => 'b@x.com',
                'origin' => 'Beta',
                'seniority' => '',
            ]),
        ];

        $catalog = SolidesInferredVacancyGrouper::buildGroupedCatalog($candidates, null, 'beta');

        $this->assertSame(1, $catalog['summary']['total']);
        $this->assertCount(1, $catalog['groups']);
        $this->assertSame('Beta', $catalog['groups'][0]['inferred_key']);
    }

    public function test_summary_counts_unidentified(): void
    {
        $candidates = [
            SolidesInferredVacancyGrouper::normalizeCurriculoRow([
                'id' => 1,
                'fullName' => 'A',
                'mainEmail' => 'a@x.com',
                'origin' => '',
                'seniority' => '',
            ]),
            SolidesInferredVacancyGrouper::normalizeCurriculoRow([
                'id' => 2,
                'fullName' => 'B',
                'mainEmail' => 'b@x.com',
                'origin' => 'Jobs',
                'seniority' => '',
            ]),
        ];

        $catalog = SolidesInferredVacancyGrouper::buildGroupedCatalog($candidates, null, null);

        $this->assertSame(2, $catalog['summary']['total']);
        $this->assertSame(1, $catalog['summary']['with_vacancy_label']);
        $this->assertSame(1, $catalog['summary']['unidentified']);
    }
}
