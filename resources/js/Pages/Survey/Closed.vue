<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({ message: String });
</script>

<template>
    <Head title="Pesquisa indisponível" />

    <div class="flex min-h-screen flex-col items-center justify-center bg-gray-50 px-4 text-center text-gray-900">
        <img src="/images/logo.png" alt="Talents" class="h-14 w-auto" />
        <h1 class="mt-6 text-xl font-semibold text-gray-900">Pesquisa indisponível</h1>
        <p class="mt-3 max-w-md text-sm text-gray-600">{{ message }}</p>
    </div>
</template>
