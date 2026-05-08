<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    board: Object,
});
</script>

<template>
    <Head title="Tarefas — Quadros" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-xl font-semibold text-gray-900">Quadro único</h2>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="route('admin.tarefas.processos.index')"
                        class="inline-flex items-center rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-800"
                    >
                        Modelos de processo
                    </Link>
                    <Link
                        :href="route('admin.tarefas.quadros.show', board.id)"
                        class="inline-flex items-center rounded-md bg-slate-100 px-3 py-2 text-sm font-medium text-slate-800"
                    >
                        Abrir quadro
                    </Link>
                </div>
            </div>
        </template>

        <div class="surface-card p-6">
            <p class="text-sm text-slate-600">
                O módulo está em modo de quadro único. Todas as tarefas são geridas neste quadro e
                compartilhadas por empresa diretamente em cada card.
            </p>
            <Link
                :href="route('admin.tarefas.quadros.show', board.id)"
                class="mt-4 inline-flex items-center rounded-md bg-talents-600 px-3 py-2 text-sm font-medium text-white"
            >
                Abrir quadro único
            </Link>
        </div>
    </AdminLayout>
</template>
