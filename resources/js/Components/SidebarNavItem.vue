<script setup>
import { computed, inject } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
    icon: {
        type: [Object, Function],
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    badge: {
        type: String,
        default: null,
    },
});

const closeMobileSidebar = inject('closeMobileSidebar', null);

const linkClasses = computed(() => {
    const base =
        'group relative flex items-center gap-3 rounded-2xl border border-transparent py-2.5 text-sm font-medium transition duration-150 ease-in-out';
    if (props.collapsed) {
        if (props.active) {
            return `${base} justify-center bg-talents-100/90 px-2 text-talents-900 shadow-sm ring-1 ring-talents-200/60`;
        }
        return `${base} justify-center px-2 text-slate-600 hover:bg-slate-100/90 hover:text-slate-900`;
    }
    if (props.active) {
        return `${base} bg-talents-100/90 px-3 text-talents-900 shadow-sm ring-1 ring-talents-300/50`;
    }
    return `${base} px-3 text-slate-600 hover:bg-slate-100/80 hover:text-slate-900`;
});

const iconClasses = computed(() =>
    props.active ? 'text-talents-700' : 'text-slate-400 group-hover:text-talents-600',
);

const onNavigate = () => {
    if (typeof closeMobileSidebar === 'function') {
        closeMobileSidebar();
    }
};
</script>

<template>
    <Link
        :href="href"
        :class="linkClasses"
        :title="collapsed ? label : undefined"
        @click="onNavigate"
    >
        <component :is="icon" class="h-5 w-5 shrink-0" :class="iconClasses" />
        <span v-if="!collapsed" class="min-w-0 flex-1 truncate">{{ label }}</span>
        <span
            v-if="badge && !collapsed"
            class="ml-auto shrink-0 rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] font-semibold text-amber-900 ring-1 ring-amber-200/80"
        >
            {{ badge }}
        </span>
    </Link>
</template>
