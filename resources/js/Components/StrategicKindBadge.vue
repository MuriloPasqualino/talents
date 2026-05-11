<script setup>
import { computed } from 'vue';

const props = defineProps({
    kind: { type: String, required: true },
    label: { type: String, required: true },
    compact: { type: Boolean, default: false },
});

const dotClass = computed(() => (props.kind === 'rito' ? 'bg-violet-500' : 'bg-sky-500'));
</script>

<template>
    <span
        class="inline-flex max-w-full items-center gap-1.5 text-slate-700"
        :class="compact ? 'text-xs' : 'text-sm'"
    >
        <span class="h-1.5 w-1.5 shrink-0 rounded-full" :class="dotClass" aria-hidden="true" />
        <span class="min-w-0 truncate font-medium">{{ label }}</span>
    </span>
</template>
