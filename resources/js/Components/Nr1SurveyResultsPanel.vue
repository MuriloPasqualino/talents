<script setup>
import { computed } from 'vue';

const props = defineProps({
    survey: Object,
    overall: Object,
    bySection: Array,
    deptOveralls: Array,
    deptSectionsByDepartment: Array,
    insights: Array,
});

const radar = computed(() => ({
    chart: { type: 'radar', toolbar: { show: false }, foreColor: '#334155' },
    colors: ['#632a7e'],
    stroke: { width: 2 },
    fill: { opacity: 0.15 },
    xaxis: {
        categories: props.bySection?.map((r) => r.meta?.section_title || 'Dimensão') ?? [],
    },
    yaxis: { show: false, min: 0, max: 100 },
    markers: { size: 4 },
    dataLabels: { enabled: true },
}));

const radarSeries = computed(() => [
    { name: 'Saúde média', data: props.bySection?.map((r) => Number(r.average_score)) ?? [] },
]);

const riskToBarColor = (level) => {
    if (level === 'green') return '#10b981';
    if (level === 'yellow') return '#f59e0b';
    return '#ef4444';
};

const deptBarChart = computed(() => {
    const rows = props.deptOveralls ?? [];
    return {
        chart: { type: 'bar', toolbar: { show: false }, foreColor: '#334155' },
        plotOptions: {
            bar: {
                borderRadius: 4,
                columnWidth: '55%',
                distributed: true,
                dataLabels: { position: 'top' },
            },
        },
        colors: rows.map((r) => riskToBarColor(r.risk_level)),
        dataLabels: { enabled: true, offsetY: -8 },
        xaxis: {
            categories: rows.map((r) => r.department_name),
        },
        yaxis: { min: 0, max: 100, title: { text: 'Saúde (0–100)' } },
        legend: { show: false },
        tooltip: { y: { formatter: (val) => `${Number(val).toFixed(1)}` } },
    };
});

const deptBarSeries = computed(() => [
    {
        name: 'Média de saúde',
        data: (props.deptOveralls ?? []).map((r) => Number(r.average_score)),
    },
]);

const deptGroupedBar = computed(() => {
    const depts = props.deptOveralls ?? [];
    const sections = props.bySection ?? [];
    const cats = depts.map((d) => d.department_name);
    const series = sections.map((sec) => {
        const sid = sec.survey_template_section_id;
        return {
            name: sec.meta?.section_title || 'Dimensão',
            data: depts.map((d) => {
                const grp = props.deptSectionsByDepartment?.find((g) => g.department_id === d.department_id);
                const row = grp?.sections?.find((s) => s.survey_template_section_id === sid);
                return row != null ? Number(row.average_score) : null;
            }),
        };
    });
    return {
        chart: { type: 'bar', toolbar: { show: false }, foreColor: '#334155' },
        plotOptions: { bar: { horizontal: false, columnWidth: '70%' } },
        xaxis: { categories: cats },
        yaxis: { min: 0, max: 100, title: { text: 'Saúde (0–100)' } },
        legend: { position: 'bottom' },
        dataLabels: { enabled: false },
        colors: ['#7b4fa2', '#b388d9', '#632a7e', '#4a2070', '#9b6bc4', '#d4b8e4', '#e8dcf2'],
        tooltip: { shared: true, intersect: false },
    };
});

const deptGroupedSeries = computed(() => {
    const depts = props.deptOveralls ?? [];
    const sections = props.bySection ?? [];
    return sections.map((sec) => {
        const sid = sec.survey_template_section_id;
        return {
            name: sec.meta?.section_title || 'Dimensão',
            data: depts.map((d) => {
                const grp = props.deptSectionsByDepartment?.find((g) => g.department_id === d.department_id);
                const row = grp?.sections?.find((s) => s.survey_template_section_id === sid);
                return row != null ? Number(row.average_score) : null;
            }),
        };
    });
});

const heatmapCellClass = (level) => {
    if (level === 'green') return 'bg-emerald-100 text-emerald-900';
    if (level === 'yellow') return 'bg-amber-100 text-amber-900';
    return 'bg-red-100 text-red-900';
};

const scoreForDeptSection = (departmentId, sectionId) => {
    const grp = props.deptSectionsByDepartment?.find((g) => g.department_id === departmentId);
    return grp?.sections?.find((s) => s.survey_template_section_id === sectionId) ?? null;
};

const healthBadge = (level) => {
    if (level === 'green') return 'bg-emerald-100 text-emerald-800';
    if (level === 'yellow') return 'bg-amber-100 text-amber-800';
    return 'bg-red-100 text-red-800';
};

const healthLevelLabel = (level) => {
    if (level === 'green') return 'Saudável';
    if (level === 'yellow') return 'Atenção';
    return 'Crítico';
};
</script>

<template>
    <div>
        <div v-if="overall" class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-talents-900">Indicador geral de saúde (0–100)</h3>
            <p class="mt-1 text-sm text-gray-500">Quanto maior, melhor o panorama psicossocial agregado.</p>
            <div class="mt-4 flex flex-wrap items-center gap-4">
                <span class="text-4xl font-bold text-talents-800">{{ Number(overall.average_score).toFixed(1) }}</span>
                <span class="rounded-full px-3 py-1 text-sm font-medium" :class="healthBadge(overall.risk_level)">
                    {{ healthLevelLabel(overall.risk_level) }}
                </span>
                <span class="text-sm text-gray-600">Respondentes: {{ overall.respondent_count }}</span>
            </div>
        </div>

        <div v-if="bySection?.length" class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-talents-900">Dimensões</h3>
            <div class="mt-4 h-96">
                <apexchart height="380" :options="radar" :series="radarSeries" />
            </div>
        </div>

        <div v-if="deptOveralls?.length" class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-talents-900">Saúde por setor (média geral)</h3>
            <p class="mt-1 text-sm text-gray-500">
                Setores só aparecem com pelo menos {{ survey.min_responses_for_breakdown }} respondentes no mesmo setor (anonimato).
            </p>
            <div class="mt-4 h-80">
                <apexchart height="320" :options="deptBarChart" :series="deptBarSeries" />
            </div>
        </div>

        <div
            v-if="deptOveralls?.length && bySection?.length && deptSectionsByDepartment?.length"
            class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <h3 class="text-lg font-semibold text-talents-900">Dimensões por setor (barras agrupadas)</h3>
            <div class="mt-4 min-h-[28rem]">
                <apexchart height="380" :options="deptGroupedBar" :series="deptGroupedSeries" />
            </div>
        </div>

        <div
            v-if="deptOveralls?.length && bySection?.length && deptSectionsByDepartment?.length"
            class="mt-8 overflow-x-auto rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
        >
            <h3 class="text-lg font-semibold text-talents-900">Tabela de saúde por setor e dimensão</h3>
            <table class="mt-4 min-w-full border-collapse text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-3 py-2 text-left font-medium text-gray-700">Setor</th>
                        <th
                            v-for="sec in bySection"
                            :key="sec.survey_template_section_id"
                            class="px-2 py-2 text-center font-medium text-gray-700"
                        >
                            {{ sec.meta?.section_title || 'Dimensão' }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in deptOveralls"
                        :key="row.department_id"
                        class="border-b border-gray-100"
                    >
                        <td class="font-medium text-gray-900">{{ row.department_name }}</td>
                        <td
                            v-for="sec in bySection"
                            :key="sec.survey_template_section_id + '-' + row.department_id"
                            class="px-2 py-2 text-center"
                        >
                            <span
                                v-if="scoreForDeptSection(row.department_id, sec.survey_template_section_id)"
                                class="inline-block min-w-[3rem] rounded px-2 py-1 font-mono text-xs"
                                :class="heatmapCellClass(scoreForDeptSection(row.department_id, sec.survey_template_section_id).risk_level)"
                            >
                                {{ Number(scoreForDeptSection(row.department_id, sec.survey_template_section_id).average_score).toFixed(1) }}
                            </span>
                            <span v-else class="text-gray-400">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-semibold text-talents-900">Insights</h3>
            <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-gray-700">
                <li v-for="i in insights" :key="i.id">{{ i.message }}</li>
                <li v-if="!insights?.length">Nenhum insight gerado ainda.</li>
            </ul>
        </div>

        <div
            v-if="overall && !deptOveralls?.length"
            class="mt-8 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900"
        >
            Não há dados por setor ainda: é necessário o número mínimo de respondentes por setor ou respostas com setor informado.
        </div>
    </div>
</template>
