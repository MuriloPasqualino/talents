<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { formatBRL } from '@/composables/useCommercialPricing';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    proposals: { type: Object, required: true },
    sellers: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const filterState = reactive({
    search: props.filters.search ?? '',
    seller_id: props.filters.seller_id ?? '',
    status: props.filters.status ?? '',
});

const applyFilters = () => {
    router.get(route('admin.comercial.propostas.index'), filterState, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterState.search = '';
    filterState.seller_id = '';
    filterState.status = '';
    applyFilters();
};

const destroy = (proposal) => {
    if (confirm(`Excluir a proposta ${proposal.code}? Essa ação não pode ser desfeita.`)) {
        router.delete(route('admin.comercial.propostas.destroy', proposal.id), { preserveScroll: true });
    }
};

const formatDate = (iso) => (iso ? new Date(iso).toLocaleDateString('pt-BR') : '—');
</script>

<template>
    <Head title="Comercial — Propostas" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-slate-500">Comercial</p>
                    <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Propostas</h2>
                </div>
                <Link
                    :href="route('admin.comercial.propostas.create')"
                    class="inline-flex items-center rounded-xl bg-talents-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-talents-700"
                >
                    Nova proposta
                </Link>
            </div>
        </template>

        <div class="surface-card p-6">
            <form class="grid gap-4 sm:grid-cols-4" @submit.prevent="applyFilters">
                <div class="sm:col-span-2">
                    <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Buscar</label>
                    <input
                        v-model="filterState.search"
                        type="text"
                        class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                        placeholder="Cliente, código ou CNPJ"
                    />
                </div>
                <div>
                    <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendedor</label>
                    <select
                        v-model="filterState.seller_id"
                        class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                    >
                        <option value="">Todos</option>
                        <option v-for="s in sellers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                    <select
                        v-model="filterState.status"
                        class="mt-1 w-full rounded-xl border-slate-300 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                    >
                        <option value="">Todos</option>
                        <option value="abertas">Em aberto</option>
                        <option value="fechadas">Fechadas</option>
                    </select>
                </div>
                <div class="sm:col-span-4 flex justify-end gap-2">
                    <button
                        type="button"
                        class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                        @click="clearFilters"
                    >
                        Limpar
                    </button>
                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    >
                        Filtrar
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 surface-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium">Código</th>
                            <th class="px-4 py-3 text-left font-medium">Cliente</th>
                            <th class="px-4 py-3 text-left font-medium">Vendedor</th>
                            <th class="px-4 py-3 text-right font-medium">Funcionários</th>
                            <th class="px-4 py-3 text-right font-medium">Total</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-right font-medium">Criada</th>
                            <th class="px-4 py-3 text-right font-medium">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <tr v-for="p in proposals.data" :key="p.id" class="hover:bg-slate-50">
                            <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ p.code }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ p.client_name }}</div>
                                <div v-if="p.client_cnpj" class="text-xs text-slate-500">{{ p.client_cnpj }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ p.seller?.name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ p.employee_count }}</td>
                            <td class="px-4 py-3 text-right tabular-nums font-semibold">
                                {{ formatBRL(p.total_final_cents) }}
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="p.is_closed ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                >
                                    {{ p.is_closed ? 'Fechada' : 'Em aberto' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-xs text-slate-500">{{ formatDate(p.created_at) }}</td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center gap-2">
                                    <Link
                                        :href="route('admin.comercial.propostas.edit', p.id)"
                                        class="text-talents-700 hover:underline"
                                    >
                                        Editar
                                    </Link>
                                    <a
                                        :href="route('admin.comercial.propostas.pdf', p.id)"
                                        class="text-slate-700 hover:underline"
                                    >
                                        PDF
                                    </a>
                                    <button
                                        type="button"
                                        class="text-rose-600 hover:underline"
                                        @click="destroy(p)"
                                    >
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!proposals.data.length">
                            <td colspan="8" class="px-4 py-10 text-center text-slate-500">
                                Nenhuma proposta encontrada.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="proposals.links?.length > 3" class="flex flex-wrap items-center justify-end gap-1 border-t border-slate-100 bg-slate-50 px-4 py-3 text-sm">
                <template v-for="link in proposals.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded-lg px-3 py-1 text-slate-700 transition hover:bg-white"
                        :class="link.active ? 'bg-talents-600 text-white hover:bg-talents-600' : ''"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="cursor-not-allowed rounded-lg px-3 py-1 text-slate-400"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </AdminLayout>
</template>
