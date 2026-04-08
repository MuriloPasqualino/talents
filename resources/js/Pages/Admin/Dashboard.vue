<script setup>
import DashboardBootstrapCalendar from '@/Components/DashboardBootstrapCalendar.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    riskBySegment: Array,
    criticalCompanies: Array,
    dashboardCalendar: Object,
});
</script>

<template>
    <Head title="Painel Admin" />

    <AdminLayout>
        <template #header>
            <div>
                <p class="text-sm text-slate-500">Olá, {{ $page.props.auth.user.name }}</p>
                <h2 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Visão geral Talents</h2>
            </div>
        </template>

        <template #aside>
            <div class="space-y-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Área admin</p>
                <div class="surface-glass space-y-3 p-4 text-sm text-slate-700">
                    <Link :href="route('admin.companies.index')" class="block font-medium text-talents-800 hover:underline">
                        Empresas
                    </Link>
                    <Link :href="route('admin.settings.edit')" class="block font-medium text-talents-800 hover:underline">
                        Configurações
                    </Link>
                </div>
            </div>
        </template>

        <div v-if="dashboardCalendar" class="mb-10">
            <DashboardBootstrapCalendar
                :items="dashboardCalendar.items"
                :year="dashboardCalendar.year"
                :month="dashboardCalendar.month"
                :kind-labels="dashboardCalendar.kindLabels"
                title="Calendário estratégico"
                subtitle="Eventos e ritos do mês — visão completa"
                :full-page-href="route('admin.strategic-calendar.index')"
                full-page-label="Gerenciar itens"
                dashboard-route="admin.dashboard"
            />
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Empresas</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ stats.companies_total }}</p>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Empresas ativas</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ stats.companies_active }}</p>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Campanhas</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ stats.surveys_total }}</p>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <p class="text-sm text-slate-500">Respostas concluídas</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ stats.responses_completed }}</p>
            </div>
        </div>

        <div class="mt-10 grid gap-8 lg:grid-cols-2">
            <div class="surface-card p-6 text-slate-900">
                <h3 class="text-lg font-semibold text-slate-900">Média de saúde por segmento (0–100)</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <li v-for="row in riskBySegment" :key="row.segment" class="flex justify-between gap-4">
                        <span>{{ row.segment }}</span>
                        <span class="font-mono tabular-nums text-slate-900">{{ Number(row.avg_score).toFixed(1) }}</span>
                    </li>
                    <li v-if="!riskBySegment?.length" class="text-slate-500">Sem dados ainda.</li>
                </ul>
            </div>
            <div class="surface-card p-6 text-slate-900">
                <h3 class="text-lg font-semibold text-slate-900">Empresas com saúde crítica (última campanha)</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-700">
                    <li v-for="c in criticalCompanies" :key="c.id">
                        {{ c.name }} <span v-if="c.segment" class="text-slate-500">({{ c.segment }})</span>
                    </li>
                    <li v-if="!criticalCompanies?.length" class="text-slate-500">Nenhuma no momento.</li>
                </ul>
            </div>
        </div>
    </AdminLayout>
</template>
