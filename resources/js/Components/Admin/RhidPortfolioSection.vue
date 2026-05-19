<script setup>
import EmptyState from '@/Components/Dashboard/EmptyState.vue';
import SectionHeader from '@/Components/Dashboard/SectionHeader.vue';
import StatCard from '@/Components/Dashboard/StatCard.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import {
    formatPortfolioBankAvg,
    operationalAlertClass,
    operationalAlertLabel,
} from '@/utils/rhidAdminMetrics';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const rhidLoading = ref(false);
const rhidError = ref(null);
const rhidData = ref(null);
const rhidLoadedAt = ref(null);

const summary = computed(() => rhidData.value?.summary ?? {});
const ranking = computed(() => rhidData.value?.ranking ?? []);
const bySegment = computed(() => rhidData.value?.by_segment ?? []);
const partial = computed(() => Boolean(rhidData.value?.partial));
const errors = computed(() => rhidData.value?.errors ?? []);

const loadRhidPortfolio = async (refresh = false) => {
    rhidLoading.value = true;
    rhidError.value = null;
    try {
        const { data } = await axios.get(route('admin.rhid.portfolio-metrics'), {
            params: refresh ? { refresh: 1 } : {},
        });
        rhidData.value = data;
        rhidLoadedAt.value = new Date();
    } catch (e) {
        rhidError.value = e?.response?.data?.message || 'Não foi possível carregar indicadores RHID.';
    } finally {
        rhidLoading.value = false;
    }
};

onMounted(() => {
    loadRhidPortfolio(false);
});

defineExpose({ loadRhidPortfolio, rhidData });
</script>

<template>
    <section class="dashboard-panel mt-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <SectionHeader
                variant="panel"
                title="Portfólio RHID"
                subtitle="Indicadores de ponto das empresas com integração ativa — mês corrente"
            />
            <div class="flex flex-wrap items-center gap-2">
                <p v-if="rhidLoadedAt" class="text-[11px] text-slate-500">
                    Atualizado
                    {{
                        rhidLoadedAt.toLocaleString('pt-BR', {
                            dateStyle: 'short',
                            timeStyle: 'medium',
                        })
                    }}
                </p>
                <PrimaryButton type="button" :disabled="rhidLoading" @click="loadRhidPortfolio(true)">
                    {{ rhidLoading ? 'Atualizando…' : 'Atualizar indicadores RHID' }}
                </PrimaryButton>
            </div>
        </div>

        <p v-if="rhidError" class="mt-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-800 ring-1 ring-rose-100">
            {{ rhidError }}
        </p>

        <div v-if="rhidLoading && !rhidData" class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="i in 4" :key="i" class="h-24 animate-pulse rounded-xl bg-slate-100" />
        </div>

        <template v-else-if="rhidData">
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    label="Empresas RHID ativas"
                    :value="summary.companies_rhid_configured ?? 0"
                    :hint="`${summary.companies_loaded ?? 0} carregadas nesta consulta`"
                />
                <StatCard
                    label="Banco médio do portfólio"
                    :value="formatPortfolioBankAvg(summary.portfolio_bank_avg_minutes)"
                    hint="Média das empresas com saldo numérico hoje"
                />
                <StatCard
                    label="Alerta operacional alto"
                    :value="`${summary.high_alert_pct ?? 0}%`"
                    :hint="`${summary.high_alert_count ?? 0} empresa(s)`"
                />
                <StatCard
                    label="Risco duplo NR-1 + RHID"
                    :value="summary.dual_risk_count ?? 0"
                    hint="NR-1 crítico e alerta operacional alto"
                />
            </div>

            <p
                v-if="partial"
                class="mt-4 rounded-lg bg-amber-50 px-4 py-2 text-xs text-amber-900 ring-1 ring-amber-100"
            >
                Algumas empresas não puderam ser consultadas ({{ errors.length }} falha(s)). Os totais refletem apenas
                as empresas carregadas com sucesso.
            </p>

            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <div>
                    <h3 class="text-sm font-semibold text-talents-800">Ranking — alerta operacional</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Top 10 empresas para priorização CS / consultoria</p>
                    <ul v-if="ranking.length" class="mt-4 divide-y divide-slate-100">
                        <li
                            v-for="row in ranking"
                            :key="row.company_id"
                            class="flex flex-wrap items-center justify-between gap-2 py-3 first:pt-0"
                        >
                            <div class="min-w-0">
                                <Link
                                    :href="route('admin.companies.show', row.company_id)"
                                    class="font-medium text-talents-900 hover:underline"
                                >
                                    {{ row.company_name }}
                                </Link>
                                <p v-if="row.segment" class="text-xs text-slate-500">{{ row.segment }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    v-if="row.dual_risk"
                                    class="rounded-full bg-rose-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white"
                                >
                                    Risco duplo
                                </span>
                                <span
                                    class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold ring-1"
                                    :class="operationalAlertClass(row.operational_alert)"
                                >
                                    {{ operationalAlertLabel(row.operational_alert) }}
                                </span>
                            </div>
                        </li>
                    </ul>
                    <EmptyState
                        v-else
                        class="mt-4 border-0 bg-transparent"
                        title="Sem empresas no ranking"
                        description="Nenhuma empresa com RHID configurado retornou métricas."
                    />
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-talents-800">Por segmento</h3>
                    <p class="mt-0.5 text-xs text-slate-500">Empresas e alertas críticos por segmento</p>
                    <ul v-if="bySegment.length" class="mt-4 space-y-2 text-sm">
                        <li
                            v-for="seg in bySegment"
                            :key="seg.segment"
                            class="flex items-center justify-between rounded-xl border border-slate-100 px-4 py-3"
                        >
                            <span class="font-medium text-slate-800">{{ seg.segment }}</span>
                            <span class="text-xs text-slate-600">
                                {{ seg.companies }} emp. · {{ seg.high_alert }} crítico(s)
                                <span v-if="seg.avg_bank_minutes != null" class="ml-1 text-slate-500">
                                    · BH {{ formatPortfolioBankAvg(seg.avg_bank_minutes) }}
                                </span>
                            </span>
                        </li>
                    </ul>
                    <EmptyState
                        v-else
                        class="mt-4 border-0 bg-transparent"
                        title="Sem segmentos"
                        description="Carregue métricas ou defina segmento nas empresas."
                    />
                </div>
            </div>
        </template>
    </section>
</template>
