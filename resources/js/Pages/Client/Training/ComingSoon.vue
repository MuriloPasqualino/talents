<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Capacitação — Em breve" />

    <ClientLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-talents-900">Capacitação de líderes</h2>
        </template>

        <div class="rounded-2xl border border-talents-200 bg-white p-10 text-center shadow-sm">
            <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">Em breve</span>
            <h3 class="mt-4 text-2xl font-bold text-talents-900">Trilhas para líderes e SESMT</h3>
            <p class="mx-auto mt-4 max-w-lg text-gray-600">
                Estamos preparando módulos de capacitação com certificados, workshops online e conteúdo alinhado à NR-1 e saúde mental
                organizacional.
            </p>
            <ul class="mx-auto mt-8 max-w-md list-disc space-y-2 pl-5 text-left text-sm text-gray-700">
                <li>Trilhas por tema (escuta, prevenção de assédio, gestão de carga)</li>
                <li>Certificados de participação</li>
                <li>Integração com indicadores das pesquisas NR-1</li>
            </ul>
        </div>
    </ClientLayout>
</template>
