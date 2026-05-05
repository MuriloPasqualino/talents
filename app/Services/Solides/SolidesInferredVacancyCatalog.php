<?php

namespace App\Services\Solides;

use App\Models\SolidesSetting;

/**
 * Carrega currículos (paginado na API) + passaportes e monta catálogo agrupado por vaga inferida.
 */
final class SolidesInferredVacancyCatalog
{
    public function __construct(
        private int $maxCurriculoPages = 50,
        private int $httpTimeoutSeconds = 90,
    ) {
        $this->maxCurriculoPages = max(1, min(100, $this->maxCurriculoPages));
        $this->httpTimeoutSeconds = max(15, min(300, $this->httpTimeoutSeconds));
    }

    /**
     * @return array{summary: array{total: int, with_vacancy_label: int, unidentified: int}, groups: list<array<string, mixed>>, curriculos_pages_fetched: int, passaportes_count: int}
     */
    public function loadGrouped(
        SolidesSetting $setting,
        ?string $dataInicial,
        ?string $dataFinal,
        ?string $originFilter,
        ?string $groupKeyContains,
    ): array {
        $client = new SolidesClient($setting);

        $baseQuery = array_filter([
            'data_inicial' => $dataInicial,
            'data_final' => $dataFinal,
        ], fn ($v) => $v !== null && $v !== '');

        $allCurriculos = [];
        $pagesFetched = 0;

        for ($page = 1; $page <= $this->maxCurriculoPages; $page++) {
            $chunk = $client->getCurriculos(array_merge($baseQuery, ['page' => $page]), $this->httpTimeoutSeconds);
            $pagesFetched = $page;
            if ($chunk === []) {
                break;
            }
            foreach ($chunk as $row) {
                if (is_array($row)) {
                    $allCurriculos[] = $row;
                }
            }
        }

        $passaportes = $client->getPassaportes([], $this->httpTimeoutSeconds);

        $merged = SolidesInferredVacancyGrouper::mergeAndDedupe($allCurriculos, $passaportes);
        $catalog = SolidesInferredVacancyGrouper::buildGroupedCatalog($merged, $originFilter, $groupKeyContains);

        return [
            'summary' => $catalog['summary'],
            'groups' => $catalog['groups'],
            'curriculos_pages_fetched' => $pagesFetched,
            'passaportes_count' => count($passaportes),
        ];
    }
}
