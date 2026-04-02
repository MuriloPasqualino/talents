<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Capacitação — Em breve" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Capacitação (LMS)</h2>
        </template>

        <div class="rounded-xl border border-gray-200 bg-white p-10 text-center shadow-sm">
            <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">Em breve</span>
            <p class="mx-auto mt-4 max-w-lg text-gray-600">
                O módulo de capacitação para empresas clientes será disponibilizado em uma próxima versão da plataforma Talents.
            </p>
        </div>
    </AdminLayout>
</template>
