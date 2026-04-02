<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    survey: Object,
    plan: Object,
});

const patchItem = (item) => {
    router.patch(
        route('client.action-plan-items.update', item.id),
        {
            responsible_name: item.responsible_name,
            due_date: item.due_date || null,
            status: item.status,
        },
        { preserveScroll: true }
    );
};

const generate = () => {
    router.post(route('client.surveys.action-plan.generate', props.survey.id));
};
</script>

<template>
    <Head title="Plano de ação" />

    <ClientLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-talents-900">Plano de ação</h2>
                <div class="flex gap-2">
                    <button type="button" class="rounded-md bg-talents-700 px-4 py-2 text-sm font-semibold text-white" @click="generate">
                        Gerar / atualizar plano
                    </button>
                    <Link :href="route('client.surveys.results', survey.id)" class="text-sm text-talents-700 hover:underline">Resultados</Link>
                </div>
            </div>
        </template>

        <div v-if="!plan" class="rounded-xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-600">
            Nenhum plano gerado ainda. Clique em "Gerar / atualizar plano".
        </div>

        <div v-else class="space-y-4">
            <div
                v-for="item in plan.items"
                :key="item.id"
                class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
            >
                <h3 class="font-semibold text-talents-900">{{ item.title }}</h3>
                <p class="mt-2 text-sm text-gray-600">{{ item.description }}</p>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <div>
                        <label class="text-xs text-gray-500">Responsável</label>
                        <input
                            v-model="item.responsible_name"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                            @change="patchItem(item)"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Prazo</label>
                        <input
                            v-model="item.due_date"
                            type="date"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                            @change="patchItem(item)"
                        />
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Status</label>
                        <select
                            v-model="item.status"
                            class="mt-1 w-full rounded-md border border-gray-300 px-2 py-1 text-sm"
                            @change="patchItem(item)"
                        >
                            <option value="pending">Pendente</option>
                            <option value="in_progress">Em andamento</option>
                            <option value="done">Concluído</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
