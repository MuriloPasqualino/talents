<script setup>
import StrategicCalendarMonthGrid from '@/Components/StrategicCalendarMonthGrid.vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    monthItems: Array,
    upcoming: Array,
    calendarYear: Number,
    calendarMonth: Number,
    kindLabels: Object,
});

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
        route('client.strategic-calendar.index'),
        { year: y, month: m },
        { preserveState: true, replace: true },
    );
};

const monthTitle = () => {
    const d = new Date(props.calendarYear, props.calendarMonth - 1, 1);
    return d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
};
</script>

<template>
    <Head title="Calendário estratégico" />

    <ClientLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-talents-900">Calendário estratégico</h2>
        </template>

        <p class="mb-6 text-sm text-slate-600">
            Eventos e ritos definidos pela Talents para orientar a jornada da sua empresa. Use as orientações em cada item
            para executar as ações no tempo certo.
        </p>

        <div class="mb-8 rounded-xl border border-talents-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-talents-300 px-2 py-1 text-sm text-talents-800 hover:bg-talents-50"
                        @click="navigateMonth(-1)"
                    >
                        ‹
                    </button>
                    <span class="min-w-[10rem] text-center text-sm font-medium capitalize text-talents-900">{{
                        monthTitle()
                    }}</span>
                    <button
                        type="button"
                        class="rounded-md border border-talents-300 px-2 py-1 text-sm text-talents-800 hover:bg-talents-50"
                        @click="navigateMonth(1)"
                    >
                        ›
                    </button>
                </div>
            </div>
            <StrategicCalendarMonthGrid
                :year="calendarYear"
                :month="calendarMonth"
                :items="monthItems"
                :kind-labels="kindLabels"
            />
        </div>

        <div class="rounded-xl border border-talents-200 bg-white p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-talents-900">Próximas datas</h3>
            <ul class="mt-4 divide-y divide-gray-100">
                <li v-for="row in upcoming" :key="row.id" class="py-4 first:pt-0">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <span
                                class="mr-2 inline-block rounded-full px-2 py-0.5 text-xs font-semibold"
                                :class="
                                    row.kind === 'rito'
                                        ? 'bg-violet-100 text-violet-800'
                                        : 'bg-sky-100 text-sky-800'
                                "
                            >
                                {{ kindLabels[row.kind] ?? row.kind }}
                            </span>
                            <span class="font-medium text-talents-900">{{ row.title }}</span>
                        </div>
                        <span class="shrink-0 text-sm text-slate-500">{{
                            row.occurs_on
                                ? new Date(row.occurs_on).toLocaleDateString('pt-BR')
                                : ''
                        }}</span>
                    </div>
                    <p v-if="row.description" class="mt-2 whitespace-pre-wrap text-sm text-slate-600">{{ row.description }}</p>
                </li>
                <li v-if="!upcoming?.length" class="py-4 text-sm text-slate-500">Nenhum item futuro cadastrado.</li>
            </ul>
        </div>
    </ClientLayout>
</template>
