<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({ surveyTitle: String });
</script>

<template>
    <Head title="Obrigado" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 text-center text-gray-900">
        <img src="/images/logo.png" alt="Talents" class="h-14 w-auto" />
        <h1 class="mt-6 text-2xl font-bold text-talents-700">Obrigado!</h1>
        <p class="mt-3 max-w-md text-sm text-gray-600">
            Suas respostas foram registradas de forma anônima para a pesquisa
            <span class="font-semibold text-gray-900">{{ surveyTitle }}</span>.
        </p>
        <p class="mt-4 text-xs text-gray-500">Você pode fechar esta página.</p>
    </div>
</template>
