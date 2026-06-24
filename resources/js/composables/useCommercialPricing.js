import { calculateCatalogProducts } from '@/composables/useCatalogProductPricing';
import { computed } from 'vue';

/**
 * Cálculo de proposta comercial em tempo real (catálogo de produtos).
 * Totais legados podem ser somados em modo edição de propostas antigas.
 *
 * @param {import('vue').Ref<object>} formRef
 * @param {import('vue').Ref<object>} settingsRef
 * @param {import('vue').Ref<Array>|import('vue').ComputedRef<Array>} catalogProductsRef
 * @param {import('vue').Ref<object>|import('vue').ComputedRef<object|null>} legacyProposalRef
 */
export function useCommercialPricing(formRef, settingsRef, catalogProductsRef = null, legacyProposalRef = null) {
    const employees = computed(() => Math.max(0, Number(formRef.value?.employee_count ?? 0)));
    const s = () => settingsRef.value || {};

    const catalog = computed(() => {
        const products = catalogProductsRef?.value ?? [];
        const selections = formRef.value?.catalog_products ?? [];
        return calculateCatalogProducts(products, employees.value, selections);
    });

    const legacySummary = computed(() => {
        const proposal = legacyProposalRef?.value;
        if (!proposal?.has_legacy_services) {
            return [];
        }
        return proposal.legacy_summary ?? [];
    });

    const legacyTotal = computed(() =>
        legacySummary.value.reduce((sum, line) => sum + Number(line.cents ?? 0), 0),
    );

    const totalFinal = computed(() => legacyTotal.value + catalog.value.total_cents);

    const commissionPercent = computed(() => Number(s().default_commission_percent ?? 0));
    const commissionCents = computed(() => Math.round(totalFinal.value * commissionPercent.value / 100));

    return {
        breakdownCents: computed(() => ({
            total_catalog_products_cents: catalog.value.total_cents,
            catalog_lines: catalog.value.lines,
            total_final_cents: totalFinal.value,
            commission_cents: commissionCents.value,
        })),
        catalogLines: computed(() => catalog.value.lines),
        legacySummary,
        totalFinalCents: totalFinal,
        commissionCents,
    };
}

/**
 * Formata centavos para uma string em reais (ex: 31172 → "R$ 311,72").
 * @param {number|null|undefined} cents
 */
export function formatBRL(cents) {
    const value = Number(cents ?? 0) / 100;
    return value.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}
