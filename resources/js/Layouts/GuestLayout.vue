<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="app-shell flex min-h-screen flex-col items-center px-4 pb-10 pt-8 sm:justify-center sm:px-6 sm:pb-12 sm:pt-6">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-16 w-16 fill-current text-talents-500 sm:h-20 sm:w-20" />
            </Link>
        </div>

        <div class="surface-glass mt-8 w-full max-w-md overflow-hidden px-6 py-8 shadow-shell sm:mt-10 sm:px-8 sm:py-10">
            <slot />
        </div>
    </div>
</template>
