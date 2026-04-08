<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="app-shell flex flex-col items-center px-4 pt-8 sm:justify-center sm:px-6 sm:pt-0">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-16 w-16 fill-current text-slate-400 sm:h-20 sm:w-20" />
            </Link>
        </div>

        <div class="surface-card mt-8 w-full max-w-md overflow-hidden px-6 py-8 sm:mt-10">
            <slot />
        </div>
    </div>
</template>
