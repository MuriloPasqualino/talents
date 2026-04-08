<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-slate-900">Dashboard</h2>
        </template>

        <div class="w-full">
            <div class="surface-card overflow-hidden p-6 text-slate-900">
                Sessão iniciada.
            </div>
        </div>
    </AuthenticatedLayout>
</template>
