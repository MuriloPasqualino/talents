<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatBRL } from '@/composables/useCommercialPricing';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    kpis: {
        type: Object,
        default: () => ({
            total_count: 0,
            closed_count: 0,
            total_budget_cents: 0,
            total_closed_cents: 0,
            avg_ticket_cents: 0,
        }),
    },
    byService: { type: Array, default: () => [] },
    bySeller: { type: Array, default: () => [] },
    recent: { type: Array, default: () => [] },
});

const formatDate = (iso) => {
    if (!iso) return '—';
    return new Date(iso).toLocaleDateString('pt-BR');
};
</script>

<template>
    <Head title="Comercial — Dashboard" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Painel Comercial</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">
                        Resumo de propostas
                    </h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('admin.comercial.propostas.index')"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                    >
                        Ver propostas
                    </Link>
                    <Link
                        :href="route('admin.comercial.propostas.create')"
                        class="inline-flex items-center rounded-xl bg-talents-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-talents-700"
                    >
                        Nova proposta
                    </Link>
                </div>
            </div>
        </template>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Propostas no funil</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ kpis.total_count }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ kpis.closed_count }} fechadas</p>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Total orçado</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ formatBRL(kpis.total_budget_cents) }}</p>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Total fechado</p>
                <p class="mt-2 text-3xl font-bold tabular-nums text-emerald-700">
                    {{ formatBRL(kpis.total_closed_cents) }}
                </p>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Ticket médio</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ formatBRL(kpis.avg_ticket_cents) }}</p>
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-2">
            <div class="surface-card p-6 text-slate-900">
                <h3 class="text-lg font-semibold text-slate-900">Resumo por serviço</h3>
                <p class="mt-1 text-xs text-slate-500">Comparativo entre orçado e fechado</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-2 pr-3 text-left font-medium">Serviço</th>
                                <th class="px-3 py-2 text-right font-medium">Orçado</th>
                                <th class="px-3 py-2 text-right font-medium">Fechado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in byService" :key="row.label">
                                <td class="py-2 pr-3 font-medium text-slate-700">{{ row.label }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    <div>{{ formatBRL(row.budget_total_cents) }}</div>
                                    <div class="text-xs text-slate-500">{{ row.budget_count }} prop.</div>
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    <div class="text-emerald-700">{{ formatBRL(row.closed_total_cents) }}</div>
                                    <div class="text-xs text-slate-500">{{ row.closed_count }} prop.</div>
                                </td>
                            </tr>
                            <tr v-if="!byService.length">
                                <td colspan="3" class="py-6 text-center text-slate-500">Nenhuma proposta cadastrada.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="surface-card p-6 text-slate-900">
                <h3 class="text-lg font-semibold text-slate-900">Resumo por vendedor</h3>
                <p class="mt-1 text-xs text-slate-500">Honorário total e comissão (apenas fechados)</p>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="text-xs uppercase tracking-wider text-slate-500">
                            <tr>
                                <th class="py-2 pr-3 text-left font-medium">Vendedor</th>
                                <th class="px-3 py-2 text-right font-medium">Orçado</th>
                                <th class="px-3 py-2 text-right font-medium">Fechado</th>
                                <th class="px-3 py-2 text-right font-medium">Comissão</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="row in bySeller" :key="row.seller_id">
                                <td class="py-2 pr-3 font-medium text-slate-700">{{ row.name }}</td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    <div>{{ formatBRL(row.budget_total_cents) }}</div>
                                    <div class="text-xs text-slate-500">{{ row.budget_count }} prop.</div>
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums">
                                    <div class="text-emerald-700">{{ formatBRL(row.closed_total_cents) }}</div>
                                    <div class="text-xs text-slate-500">{{ row.closed_count }} prop.</div>
                                </td>
                                <td class="px-3 py-2 text-right tabular-nums text-violet-700">
                                    {{ formatBRL(row.commission_total_cents) }}
                                </td>
                            </tr>
                            <tr v-if="!bySeller.length">
                                <td colspan="4" class="py-6 text-center text-slate-500">Nenhum vendedor cadastrado.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-10 surface-card p-6 text-slate-900">
            <h3 class="text-lg font-semibold text-slate-900">Últimas propostas</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-2 pr-3 text-left font-medium">Código</th>
                            <th class="px-3 py-2 text-left font-medium">Cliente</th>
                            <th class="px-3 py-2 text-left font-medium">Vendedor</th>
                            <th class="px-3 py-2 text-right font-medium">Funcionários</th>
                            <th class="px-3 py-2 text-right font-medium">Total</th>
                            <th class="px-3 py-2 text-left font-medium">Status</th>
                            <th class="px-3 py-2 text-right font-medium">Criada</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="p in recent" :key="p.id" class="hover:bg-slate-50">
                            <td class="py-2 pr-3 font-mono text-xs text-slate-600">
                                <Link :href="route('admin.comercial.propostas.edit', p.id)" class="hover:underline">
                                    {{ p.code }}
                                </Link>
                            </td>
                            <td class="px-3 py-2 font-medium">{{ p.client_name }}</td>
                            <td class="px-3 py-2 text-slate-600">{{ p.seller?.name ?? '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums">{{ p.employee_count }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold">
                                {{ formatBRL(p.total_final_cents) }}
                            </td>
                            <td class="px-3 py-2">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="p.is_closed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                >
                                    {{ p.is_closed ? 'Fechada' : 'Em aberto' }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-right text-xs text-slate-500">{{ formatDate(p.created_at) }}</td>
                        </tr>
                        <tr v-if="!recent.length">
                            <td colspan="7" class="py-6 text-center text-slate-500">Nenhuma proposta ainda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
