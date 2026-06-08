<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="mt-3 first:mt-0">
        <p
            v-if="!collapsed"
            class="mb-1.5 px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400"
        >
            {{ label }}
        </p>
        <div v-else class="my-2 border-t border-slate-200/80" aria-hidden="true" />
        <div class="space-y-0.5">
            <slot />
        </div>
    </div>
</template>
