<script setup>
import { computed } from 'vue';

const props = defineProps({
    year: { type: Number, required: true },
    month: { type: Number, required: true },
    items: { type: Array, default: () => [] },
    kindLabels: { type: Object, default: () => ({}) },
});

const monthLabel = computed(() => {
    const d = new Date(props.year, props.month - 1, 1);
    return d.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
});

const itemsByDay = computed(() => {
    const map = {};
    for (const item of props.items) {
        const key = item.occurs_on?.slice?.(0, 10) ?? String(item.occurs_on);
        if (!map[key]) map[key] = [];
        map[key].push(item);
    }
    return map;
});

/** @returns {Array<{ day: number|null, iso: string|null, items: object[] }>} */
const weeks = computed(() => {
    const y = props.year;
    const m = props.month;
    const first = new Date(y, m - 1, 1);
    const startPad = first.getDay();
    const daysInMonth = new Date(y, m, 0).getDate();
    const cells = [];
    for (let i = 0; i < startPad; i++) {
        cells.push({ day: null, iso: null, items: [] });
    }
    for (let d = 1; d <= daysInMonth; d++) {
        const iso = `${y}-${String(m).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        cells.push({
            day: d,
            iso,
            items: itemsByDay.value[iso] ?? [],
        });
    }
    while (cells.length % 7 !== 0) {
        cells.push({ day: null, iso: null, items: [] });
    }
    const rows = [];
    for (let i = 0; i < cells.length; i += 7) {
        rows.push(cells.slice(i, i + 7));
    }
    return rows;
});

const weekdayLabels = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];

const kindLabel = (kind) => props.kindLabels[kind] ?? kind;
</script>

<template>
    <div class="overflow-x-auto">
        <p class="mb-3 text-center text-sm font-semibold capitalize text-gray-800">{{ monthLabel }}</p>
        <table class="w-full min-w-[640px] border-collapse text-left text-xs">
            <thead>
                <tr>
                    <th
                        v-for="w in weekdayLabels"
                        :key="w"
                        class="border border-gray-200 bg-gray-50 px-1 py-2 text-center font-medium text-gray-600"
                    >
                        {{ w }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, ri) in weeks" :key="ri">
                    <td
                        v-for="(cell, ci) in row"
                        :key="ci"
                        class="align-top border border-gray-200 bg-white p-1 min-h-[5rem] w-[14.28%]"
                        :class="cell.day ? 'text-gray-900' : 'bg-gray-50'"
                    >
                        <span v-if="cell.day" class="font-medium text-gray-700">{{ cell.day }}</span>
                        <ul v-if="cell.items.length" class="mt-1 space-y-0.5">
                            <li v-for="it in cell.items" :key="it.id" class="leading-tight">
                                <span
                                    class="mr-0.5 inline-block rounded px-0.5 text-[10px] font-semibold uppercase"
                                    :class="
                                        it.kind === 'rito'
                                            ? 'bg-violet-100 text-violet-800'
                                            : 'bg-sky-100 text-sky-800'
                                    "
                                >
                                    {{ kindLabel(it.kind) }}
                                </span>
                                <span class="text-gray-800">{{ it.title }}</span>
                            </li>
                        </ul>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
