<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: String,
    companyName: String,
});

const form = useForm({
    protocol: '',
});

const submit = () => {
    form.post(route('denuncia.track.lookup', props.token));
};
</script>

<template>
    <Head title="Acompanhar denúncia" />

    <div class="min-h-screen bg-slate-100 px-4 py-12 text-gray-900">
        <div class="mx-auto max-w-md rounded-xl border border-gray-200 bg-white p-8 shadow-sm">
            <h1 class="text-lg font-semibold text-gray-900">Acompanhar denúncia</h1>
            <p class="mt-1 text-sm text-gray-600">{{ companyName }}</p>

            <form class="mt-6 space-y-4" @submit.prevent="submit">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Protocolo (UUID)</label>
                    <input
                        v-model="form.protocol"
                        type="text"
                        required
                        class="mt-1 w-full rounded-md border-gray-300 font-mono text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                        placeholder="00000000-0000-0000-0000-000000000000"
                    />
                    <p v-if="form.errors.protocol" class="mt-1 text-sm text-red-600">{{ form.errors.protocol }}</p>
                </div>
                <button
                    type="submit"
                    class="w-full rounded-lg bg-talents-700 py-2 text-sm font-semibold text-white hover:bg-talents-800"
                    :disabled="form.processing"
                >
                    Consultar
                </button>
            </form>

            <p class="mt-6 text-center text-sm">
                <Link :href="route('denuncia.create', token)" class="text-talents-700 hover:underline">Fazer uma denúncia</Link>
            </p>
        </div>
    </div>
</template>
