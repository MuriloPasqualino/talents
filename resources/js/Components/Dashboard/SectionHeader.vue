<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    /** 'panel' = título mais destacado (estilo relatório) */
    variant: { type: String, default: 'default' },
});
</script>

<template>
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div class="min-w-0">
            <h3
                class="tracking-tight text-slate-900"
                :class="variant === 'panel' ? 'font-serif text-xl font-bold sm:text-2xl' : 'text-base font-semibold'"
            >
                {{ title }}
            </h3>
            <p v-if="subtitle" class="mt-0.5 text-xs text-slate-500">{{ subtitle }}</p>
        </div>
        <div v-if="$slots.action" class="shrink-0">
            <slot name="action" />
        </div>
    </div>
</template>
