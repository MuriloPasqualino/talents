<script setup>
import HeroStrategicCalendar from '@/Components/HeroStrategicCalendar.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    items: { type: Array, default: () => [] },
    year: { type: Number, required: true },
    month: { type: Number, required: true },
    kindLabels: { type: Object, default: () => ({ event: 'Evento', rito: 'Rito' }) },
    title: { type: String, default: 'Calendário estratégico' },
    subtitle: { type: String, default: null },
    fullPageHref: { type: String, default: null },
    fullPageLabel: { type: String, default: 'Abrir calendário completo' },
    /** Nome da rota Ziggy (ex.: admin.dashboard ou client.dashboard) */
    dashboardRoute: { type: String, required: true },
    queryParamYear: { type: String, default: 'cal_year' },
    queryParamMonth: { type: String, default: 'cal_month' },
});

const localYear = ref(props.year);
const localMonth = ref(props.month);

watch(
    () => [props.year, props.month],
    ([y, m]) => {
        localYear.value = y;
        localMonth.value = m;
    },
);

const goMonth = (delta) => {
    let y = localYear.value;
    let mo = localMonth.value + delta;
    if (mo < 1) {
        mo = 12;
        y -= 1;
    } else if (mo > 12) {
        mo = 1;
        y += 1;
    }
    router.get(
        route(props.dashboardRoute),
        {
            [props.queryParamYear]: y,
            [props.queryParamMonth]: mo,
        },
        { preserveScroll: true, replace: true },
    );
};

const goToday = () => {
    const t = new Date();
    router.get(
        route(props.dashboardRoute),
        {
            [props.queryParamYear]: t.getFullYear(),
            [props.queryParamMonth]: t.getMonth() + 1,
        },
        { preserveScroll: true, replace: true },
    );
};
</script>

<template>
    <div class="overflow-hidden rounded-2xl border border-zinc-200/90 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-zinc-100 p-4 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">{{ title }}</h3>
                <p v-if="subtitle" class="mt-0.5 text-sm text-zinc-500">{{ subtitle }}</p>
            </div>
            <Link
                v-if="fullPageHref"
                :href="fullPageHref"
                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-talents-700 px-3 py-1.5 text-sm font-semibold text-white hover:bg-talents-800"
            >
                {{ fullPageLabel }}
            </Link>
        </div>
        <div class="p-3 sm:p-4">
            <HeroStrategicCalendar
                :year="localYear"
                :month="localMonth"
                :items="items"
                :kind-labels="kindLabels"
                compact
                @navigate-month="goMonth"
                @go-today="goToday"
            />
        </div>
    </div>
</template>
