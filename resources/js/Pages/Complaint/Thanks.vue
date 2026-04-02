<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    token: String,
    companyName: String,
    protocol: String,
});
</script>

<template>
    <Head title="Denúncia registrada" />

    <div class="min-h-screen bg-slate-100 px-4 py-12 text-gray-900">
        <div class="mx-auto max-w-lg rounded-xl border border-emerald-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-semibold text-emerald-800">Registro concluído</h1>
            <p class="mt-2 text-sm text-gray-600">{{ companyName }}</p>
            <p class="mt-6 text-sm text-gray-700">Guarde o número de protocolo:</p>
            <p class="mt-2 break-all rounded-lg bg-gray-50 p-3 font-mono text-sm font-semibold text-talents-900">{{ protocol }}</p>
            <p class="mt-4 text-xs text-gray-500">
                Use-o para acompanhar o andamento, sem precisar informar nome ou e-mail (se optou por anonimato).
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <Link
                    :href="route('denuncia.protocol', { token, protocol })"
                    class="inline-flex justify-center rounded-lg bg-talents-700 px-4 py-2 text-sm font-semibold text-white hover:bg-talents-800"
                >
                    Acompanhar agora
                </Link>
                <Link :href="route('denuncia.create', token)" class="text-center text-sm text-talents-700 hover:underline">Nova denúncia</Link>
            </div>
        </div>
    </div>
</template>
