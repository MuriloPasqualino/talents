<script setup>
import StrategicCalendarMonthGrid from '@/Components/StrategicCalendarMonthGrid.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    items: Object,
    monthItems: Array,
    calendarYear: Number,
    calendarMonth: Number,
    filters: Object,
    companies: Array,
    kindLabels: Object,
});

const companyFilter = ref(props.filters?.company_id ? String(props.filters.company_id) : '');

const navigateMonth = (delta) => {
    let y = props.calendarYear;
    let m = props.calendarMonth + delta;
    if (m < 1) {
        m = 12;
        y -= 1;
    } else if (m > 12) {
        m = 1;
        y += 1;
    }
    router.get(
        route('admin.strategic-calendar.index'),
        { year: y, month: m, company_id: companyFilter.value || undefined },
        { preserveState: true, replace: true },
    );
};

watch(companyFilter, (v) => {
    router.get(
        route('admin.strategic-calendar.index'),
        {
            year: props.calendarYear,
            month: props.calendarMonth,
            company_id: v || undefined,
        },
        { preserveState: true, replace: true },
    );
});

const monthTitle = computed(() => {
    const d = new Date(props.calendarYear, props.calendarMonth - 1, 1);
    return d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
});
</script>

<template>
    <Head title="Calendário estratégico" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Calendário estratégico</h2>
                <Link
                    :href="route('admin.strategic-calendar.create')"
                    class="inline-flex items-center justify-center rounded-md bg-talents-700 px-4 py-2 text-sm font-semibold text-white hover:bg-talents-800"
                >
                    Novo evento ou rito
                </Link>
            </div>
        </template>

        <div class="mb-8 rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
                        @click="navigateMonth(-1)"
                    >
                        ‹
                    </button>
                    <span class="min-w-[10rem] text-center text-sm font-medium capitalize text-gray-800">{{ monthTitle }}</span>
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 px-2 py-1 text-sm text-gray-700 hover:bg-gray-50"
                        @click="navigateMonth(1)"
                    >
                        ›
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Filtrar por empresa</label>
                    <select
                        v-model="companyFilter"
                        class="rounded-md border border-gray-300 text-sm text-gray-900 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                    >
                        <option value="">Todas</option>
                        <option v-for="c in companies" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <StrategicCalendarMonthGrid
                    :year="calendarYear"
                    :month="calendarMonth"
                    :items="monthItems"
                    :kind-labels="kindLabels"
                />
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Data</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Tipo</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Título</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Empresa</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="row in items.data" :key="row.id">
                        <td class="whitespace-nowrap px-4 py-3 text-gray-800">
                            {{
                                row.occurs_on
                                    ? new Date(row.occurs_on).toLocaleDateString('pt-BR')
                                    : '—'
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="
                                    row.kind === 'rito'
                                        ? 'bg-violet-100 text-violet-800'
                                        : 'bg-sky-100 text-sky-800'
                                "
                            >
                                {{ kindLabels[row.kind] ?? row.kind }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-900">{{ row.title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ row.company?.name ?? 'Todas' }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <Link
                                :href="route('admin.strategic-calendar.edit', row.id)"
                                class="font-medium text-talents-700 hover:underline"
                            >
                                Editar
                            </Link>
                            <Link
                                :href="route('admin.strategic-calendar.destroy', row.id)"
                                method="delete"
                                as="button"
                                class="ml-3 font-medium text-red-600 hover:underline"
                                preserve-scroll
                            >
                                Excluir
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!items.data?.length">
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Nenhum item cadastrado.</td>
                    </tr>
                </tbody>
            </table>
            <div v-if="items.links?.length > 3" class="border-t border-gray-100 px-4 py-3">
                <div class="flex flex-wrap gap-1">
                    <template v-for="(l, i) in items.links" :key="i">
                        <Link
                            v-if="l.url"
                            :href="l.url"
                            class="rounded px-2 py-1 text-sm"
                            :class="l.active ? 'bg-talents-700 text-white' : 'text-talents-700 hover:bg-gray-100'"
                            preserve-scroll
                            v-html="l.label"
                        />
                        <span
                            v-else
                            class="rounded px-2 py-1 text-sm text-gray-400"
                            v-html="l.label"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
