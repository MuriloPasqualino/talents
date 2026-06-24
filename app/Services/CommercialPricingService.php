<?php

namespace App\Services;

use App\Models\CommercialProduct;
use App\Models\CommercialProposal;
use App\Models\CommercialSetting;
use App\Services\Commercial\CommercialProductPricingService;
use Illuminate\Support\Collection;

/**
 * Cálculo de propostas comerciais com base no catálogo de produtos.
 * Totais legados (svc_*) são preservados apenas em propostas históricas.
 */
class CommercialPricingService
{
    public function __construct(
        private ?CommercialProductPricingService $catalogPricing = null,
    ) {}

    private function catalogPricingService(): CommercialProductPricingService
    {
        return $this->catalogPricing ??= new CommercialProductPricingService;
    }

    /**
     * Calcula o breakdown de uma proposta (somente catálogo de produtos).
     *
     * @param  array<string, mixed>  $inputs
     * @return array<string, int|float|array>
     */
    public function calculate(array $inputs, ?CommercialSetting $settings = null): array
    {
        $s = $settings ?? CommercialSetting::current();
        $employees = max(0, (int) ($inputs['employee_count'] ?? 0));
        $catalogSelections = $inputs['catalog_products'] ?? [];

        $catalog = $this->catalogPricingService()->calculateMany(
            $this->catalogProducts($inputs, $catalogSelections),
            $employees,
            $catalogSelections,
        );

        $totalFinal = $catalog['total_cents'];

        $commissionPercent = (float) ($inputs['commission_percent'] ?? $s->default_commission_percent ?? 0);
        $commissionCents = (int) round($totalFinal * $commissionPercent / 100);

        return [
            'total_pesquisas_cents' => 0,
            'total_profiler_cents' => 0,
            'total_devolutiva_cents' => 0,
            'total_nr1_cents' => 0,
            'total_nr1_implantacao_cents' => 0,
            'total_contratacao_cents' => 0,
            'total_direcionamento_cents' => 0,
            'total_palestras_cents' => 0,
            'total_catalog_products_cents' => $catalog['total_cents'],
            'catalog_lines' => $catalog['lines'],
            'total_final_cents' => $totalFinal,
            'commission_percent' => $commissionPercent,
            'commission_cents' => $commissionCents,
        ];
    }

    /**
     * Recalcula catálogo e preserva totais legados já gravados (propostas antigas).
     *
     * @param  array<string, mixed>  $inputs
     * @return array<string, int|float|array>
     */
    public function calculatePreservingLegacy(CommercialProposal $proposal, array $inputs, ?CommercialSetting $settings = null): array
    {
        $result = $this->calculate($inputs, $settings);

        if (! $proposal->hasLegacyServices()) {
            return $result;
        }

        $legacyTotals = [
            'total_pesquisas_cents' => (int) $proposal->total_pesquisas_cents,
            'total_profiler_cents' => (int) $proposal->total_profiler_cents,
            'total_devolutiva_cents' => (int) $proposal->total_devolutiva_cents,
            'total_nr1_cents' => (int) $proposal->total_nr1_cents,
            'total_nr1_implantacao_cents' => (int) $proposal->total_nr1_implantacao_cents,
            'total_contratacao_cents' => (int) $proposal->total_contratacao_cents,
            'total_direcionamento_cents' => (int) $proposal->total_direcionamento_cents,
            'total_palestras_cents' => (int) $proposal->total_palestras_cents,
        ];

        $totalFinal = $proposal->legacyTotalsCents() + (int) $result['total_catalog_products_cents'];
        $commissionPercent = (float) $result['commission_percent'];
        $commissionCents = (int) round($totalFinal * $commissionPercent / 100);

        return array_merge($result, $legacyTotals, [
            'total_final_cents' => $totalFinal,
            'commission_cents' => $commissionCents,
        ]);
    }

    /**
     * @param  array<string, mixed>  $inputs
     * @param  array<int, array<string, mixed>>  $selections
     */
    private function catalogProducts(array $inputs, array $selections): Collection
    {
        if (isset($inputs['_catalog_products']) && $inputs['_catalog_products'] instanceof Collection) {
            return $inputs['_catalog_products'];
        }

        if ($selections === []) {
            return collect();
        }

        $ids = array_values(array_unique(array_filter(array_map(
            fn ($s) => (int) ($s['product_id'] ?? 0),
            $selections,
        ))));

        if ($ids === []) {
            return collect();
        }

        return CommercialProduct::query()
            ->active()
            ->whereIn('id', $ids)
            ->ordered()
            ->get();
    }
}
