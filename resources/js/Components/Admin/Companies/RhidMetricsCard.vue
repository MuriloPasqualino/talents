<script setup>
import RhidOverviewKpiCards from '@/Components/Rhid/RhidOverviewKpiCards.vue';
import HealthBadge from '@/Components/Dashboard/HealthBadge.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { metricsToKpiProps, operationalAlertClass, operationalAlertLabel } from '@/utils/rhidAdminMetrics';
import { formatRhidBankBalanceMinutes } from '@/utils/rhidDate';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    companyId: { type: Number, required: true },
    rhidConfigured: { type: Boolean, default: false },
});

const loading = ref(false);
const error = ref(null);
const metrics = ref(null);
const loadedAt = ref(null);

const kpiProps = computed(() => metricsToKpiProps(metrics.value));

const loadMetrics = async (refresh = false) => {
    if (!props.rhidConfigured) {
        return;
    }
    loading.value = true;
    error.value = null;
    try {
        const { data } = await axios.get(route('admin.companies.rhid-metrics', props.companyId), {
            params: refresh ? { refresh: 1 } : {},
        });
        metrics.value = data;
        loadedAt.value = new Date();
    } catch (e) {
        error.value = e?.response?.data?.message || 'Falha ao carregar indicadores RHID.';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    loadMetrics(false);
});
</script>

<template>
    <div class="surface-card p-6 text-slate-900 lg:col-span-2">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h3 class="font-semibold text-talents-700">Indicadores RHID (mês atual)</h3>
                <p class="mt-1 text-xs text-slate-500">
                    Banco de horas na data de hoje · aderência e justificativas no mês civil corrente.
                </p>
            </div>
            <PrimaryButton
                v-if="rhidConfigured"
                type="button"
                :disabled="loading"
                class="shrink-0"
                @click="loadMetrics(true)"
            >
                {{ loading ? 'Atualizando…' : 'Atualizar' }}
            </PrimaryButton>
        </div>

        <p v-if="!rhidConfigured" class="mt-4 rounded-lg bg-slate-50 px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-100">
            Integração RHID não configurada. O cliente deve informar credenciais em RHID / Ponto no portal.
        </p>

        <p v-else-if="error" class="mt-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-100">
            {{ error }}
        </p>

        <template v-else-if="rhidConfigured">
            <div
                v-if="metrics?.status === 'ok'"
                class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm"
            >
                <div class="flex items-center gap-2">
                    <span class="text-xs font-medium uppercase text-slate-500">Alerta operacional</span>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1"
                        :class="operationalAlertClass(metrics.operational_alert)"
                    >
                        {{ operationalAlertLabel(metrics.operational_alert) }}
                    </span>
                </div>
                <div v-if="metrics.nr1?.risk_level" class="flex items-center gap-2">
                    <span class="text-xs font-medium uppercase text-slate-500">NR-1 (última campanha)</span>
                    <HealthBadge :risk-level="metrics.nr1.risk_level" />
                    <span v-if="metrics.nr1.average_score != null" class="text-xs tabular-nums text-slate-600">
                        {{ Number(metrics.nr1.average_score).toFixed(1) }}
                    </span>
                </div>
                <span
                    v-if="metrics.dual_risk"
                    class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white"
                >
                    Risco duplo
                </span>
                <span v-if="!metrics.integration?.ok" class="text-xs text-amber-800">
                    Última falha API: {{ metrics.integration?.last_error || 'verifique credenciais' }}
                </span>
            </div>

            <p v-if="metrics?.adherence?.diagnostics_hint" class="mt-3 text-xs text-amber-800">
                {{ metrics.adherence.diagnostics_hint }}
            </p>

            <div v-if="kpiProps" class="mt-5">
                <RhidOverviewKpiCards
                    :loading="loading && !metrics"
                    :interactive="false"
                    v-bind="kpiProps"
                    :format-rhid-bank-balance-minutes="formatRhidBankBalanceMinutes"
                />
            </div>

            <div
                v-if="metrics?.bank?.worst_three?.length"
                class="mt-6 border-t border-slate-100 pt-4"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Piores saldos de banco</p>
                <ul class="mt-2 space-y-1 text-sm">
                    <li v-for="(w, i) in metrics.bank.worst_three" :key="i" class="flex justify-between gap-2">
                        <span class="truncate text-slate-800">{{ w.name }}</span>
                        <span class="shrink-0 font-mono text-rose-700">{{ w.display }}</span>
                    </li>
                </ul>
            </div>

            <p v-if="loadedAt" class="mt-4 text-[11px] text-slate-400">
                Atualizado
                {{
                    loadedAt.toLocaleString('pt-BR', {
                        dateStyle: 'short',
                        timeStyle: 'medium',
                    })
                }}
            </p>
        </template>
    </div>
</template>
