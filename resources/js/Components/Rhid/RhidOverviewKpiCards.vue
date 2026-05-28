<script setup>
import { computed } from 'vue';

const props = defineProps({
    loading: { type: Boolean, default: false },
    interactive: { type: Boolean, default: true },
    overviewPunchRowsLength: { type: Number, default: 0 },
    overviewPunchDistinct: { type: Number, default: 0 },
    overviewBankNumericRowsLength: { type: Number, default: 0 },
    overviewBankNegativeCount: { type: Number, default: 0 },
    overviewBankAvgMinutes: { type: [Number, null], default: null },
    overviewBankAvgMomDeltaMinutes: { type: [Number, null], default: null },
    overviewAdherenceDiasCalendario: { type: [Number, null], default: null },
    overviewAdherenceColabs: { type: Number, default: 0 },
    overviewAdherenceDiasMomDelta: { type: [Number, null], default: null },
    overviewAdherenceColabsMomDelta: { type: [Number, null], default: null },
    overviewJustTotal: { type: [Number, null], default: null },
    overviewJustAtestados: { type: [Number, null], default: null },
    overviewJustTotalMomDelta: { type: [Number, null], default: null },
    overviewJustAtestadosMomDelta: { type: [Number, null], default: null },
    formatRhidBankBalanceMinutes: { type: Function, required: true },
    bankHoursLabel: { type: String, default: 'Banco de horas (hoje)' },
});

const emit = defineEmits(['go-punches-adherence', 'go-bank', 'go-justifications']);

const isFiniteNumber = (v) => typeof v === 'number' && Number.isFinite(v);

const signedIntTxt = (n) => {
    if (n == null || Number.isNaN(Number(n))) {
        return null;
    }
    const v = Number(n);
    if (v === 0) {
        return '0';
    }
    return v > 0 ? `+${v}` : `${v}`;
};

const overviewBankAvgHHmm = computed(() => {
    if (!isFiniteNumber(props.overviewBankAvgMinutes)) {
        return '—';
    }
    return props.formatRhidBankBalanceMinutes(props.overviewBankAvgMinutes);
});

const overviewBankAvgIsNegative = computed(
    () => isFiniteNumber(props.overviewBankAvgMinutes) && props.overviewBankAvgMinutes < 0,
);

const trendInfo = (delta, inverse = false) => {
    if (!isFiniteNumber(delta) || delta === 0) {
        return { txt: signedIntTxt(delta), cls: 'text-white/85', arrow: '→' };
    }
    const positive = inverse ? delta < 0 : delta > 0;
    return {
        txt: signedIntTxt(delta),
        cls: positive ? 'text-emerald-200' : 'text-rose-200',
        arrow: delta > 0 ? '↑' : delta < 0 ? '↓' : '→',
    };
};

const trendBank = computed(() => {
    const m = props.overviewBankAvgMomDeltaMinutes;
    if (!isFiniteNumber(m)) {
        return null;
    }
    return {
        txt: props.formatRhidBankBalanceMinutes(m),
        cls: m >= 0 ? 'text-emerald-200' : 'text-rose-200',
        arrow: m > 0 ? '↑' : m < 0 ? '↓' : '→',
    };
});
</script>

<template>
    <div v-if="loading" class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <div
            v-for="i in 4"
            :key="i"
            class="h-32 animate-pulse rounded-2xl border border-slate-100 bg-gradient-to-br from-slate-50 to-slate-100"
        />
    </div>

    <div v-else class="grid gap-3 md:grid-cols-2 xl:grid-cols-4">
        <component
            :is="interactive ? 'button' : 'div'"
            :type="interactive ? 'button' : undefined"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-talents-700 via-talents-600 to-talents-500 p-4 text-left text-white shadow-md transition"
            :class="interactive ? 'hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-talents-300 focus:ring-offset-2' : ''"
            @click="interactive ? emit('go-punches-adherence') : undefined"
        >
            <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl" />
            <div class="relative z-10">
                <p class="text-[11px] font-medium uppercase tracking-wider text-white/75">Marcações RHID</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">{{ overviewPunchRowsLength }}</p>
                <p class="mt-0.5 text-xs text-white/80">{{ overviewPunchDistinct }} colab. distintos</p>
            </div>
            <p v-if="interactive" class="relative z-10 mt-3 text-[11px] font-medium text-white/85">
                Última leitura · aderência ao horário →
            </p>
        </component>

        <component
            :is="interactive ? 'button' : 'div'"
            :type="interactive ? 'button' : undefined"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-talents-800 via-talents-700 to-talents-600 p-4 text-left text-white shadow-md transition"
            :class="interactive ? 'hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-talents-300 focus:ring-offset-2' : ''"
            @click="interactive ? emit('go-bank') : undefined"
        >
            <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/10 blur-xl" />
            <div class="relative z-10">
                <p class="text-[11px] font-medium uppercase tracking-wider text-white/75">{{ bankHoursLabel }}</p>
                <p
                    class="mt-2 text-3xl font-bold tabular-nums"
                    :class="overviewBankAvgIsNegative ? 'text-rose-200' : 'text-white'"
                >
                    {{ overviewBankAvgHHmm }}
                </p>
                <p class="mt-0.5 text-xs text-white/80">Média de {{ overviewBankNumericRowsLength }} colaborador(es)</p>
            </div>
            <div class="relative z-10 mt-3 flex flex-wrap items-center gap-x-3 gap-y-1">
                <span
                    v-if="trendBank"
                    class="inline-flex items-center gap-1 text-[11px] font-semibold"
                    :class="trendBank.cls"
                >
                    <span>{{ trendBank.arrow }}</span>
                    <span>{{ trendBank.txt }}</span>
                    <span class="font-normal text-white/65">vs. mês anterior</span>
                </span>
                <span
                    v-if="overviewBankNegativeCount > 0"
                    class="inline-flex items-center gap-1 rounded-full bg-rose-500/30 px-2 py-0.5 text-[10px] font-medium text-rose-100"
                >
                    {{ overviewBankNegativeCount }} negativos
                </span>
            </div>
        </component>

        <component
            :is="interactive ? 'button' : 'div'"
            :type="interactive ? 'button' : undefined"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-talents-500 via-talents-400 to-talents-accent p-4 text-left text-white shadow-md transition"
            :class="interactive ? 'hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-talents-300 focus:ring-offset-2' : ''"
            @click="interactive ? emit('go-punches-adherence') : undefined"
        >
            <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/15 blur-xl" />
            <div class="relative z-10">
                <p class="text-[11px] font-medium uppercase tracking-wider text-white/85">Aderência (mês)</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">
                    {{ overviewAdherenceDiasCalendario != null ? overviewAdherenceDiasCalendario : '—' }}
                    <span class="text-base font-semibold text-white/85">dias</span>
                </p>
                <p class="mt-0.5 text-xs text-white/85">{{ overviewAdherenceColabs }} colab. com dados</p>
            </div>
            <div class="relative z-10 mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px]">
                <span
                    v-if="overviewAdherenceDiasMomDelta != null"
                    class="inline-flex items-center gap-1 font-semibold"
                    :class="trendInfo(overviewAdherenceDiasMomDelta).cls"
                >
                    <span>{{ trendInfo(overviewAdherenceDiasMomDelta).arrow }}</span>
                    {{ signedIntTxt(overviewAdherenceDiasMomDelta) }} dias
                </span>
                <span
                    v-if="overviewAdherenceColabsMomDelta != null"
                    class="inline-flex items-center gap-1 font-semibold"
                    :class="trendInfo(overviewAdherenceColabsMomDelta).cls"
                >
                    <span>{{ trendInfo(overviewAdherenceColabsMomDelta).arrow }}</span>
                    {{ signedIntTxt(overviewAdherenceColabsMomDelta) }} colab.
                </span>
            </div>
        </component>

        <component
            :is="interactive ? 'button' : 'div'"
            :type="interactive ? 'button' : undefined"
            class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 via-amber-400 to-yellow-400 p-4 text-left text-white shadow-md transition"
            :class="interactive ? 'hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-amber-200 focus:ring-offset-2' : ''"
            @click="interactive ? emit('go-justifications') : undefined"
        >
            <div class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-white/20 blur-xl" />
            <div class="relative z-10">
                <p class="text-[11px] font-medium uppercase tracking-wider text-white/85">Justificativas</p>
                <p class="mt-2 text-3xl font-bold tabular-nums">
                    {{ overviewJustTotal != null ? overviewJustTotal : '—' }}
                </p>
                <p class="mt-0.5 text-xs text-white/85">
                    Atestados na 1ª pág.:
                    <span class="font-semibold">{{ overviewJustAtestados != null ? overviewJustAtestados : '—' }}</span>
                </p>
            </div>
            <div class="relative z-10 mt-3 flex flex-wrap items-center gap-x-2 gap-y-1 text-[11px]">
                <span
                    v-if="overviewJustTotalMomDelta != null"
                    class="inline-flex items-center gap-1 font-semibold"
                    :class="trendInfo(overviewJustTotalMomDelta, true).cls"
                >
                    <span>{{ trendInfo(overviewJustTotalMomDelta, true).arrow }}</span>
                    {{ signedIntTxt(overviewJustTotalMomDelta) }} total
                </span>
                <span
                    v-if="overviewJustAtestadosMomDelta != null"
                    class="inline-flex items-center gap-1 font-semibold"
                    :class="trendInfo(overviewJustAtestadosMomDelta, true).cls"
                >
                    <span>{{ trendInfo(overviewJustAtestadosMomDelta, true).arrow }}</span>
                    {{ signedIntTxt(overviewJustAtestadosMomDelta) }} atest.
                </span>
            </div>
        </component>
    </div>
</template>
