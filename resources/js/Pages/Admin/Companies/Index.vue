<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    companies: Object,
    filters: Object,
});

const form = useForm({
    search: props.filters?.search ?? '',
});

const submit = () => {
    form.get(route('admin.companies.index'), { preserveState: true });
};
</script>

<template>
    <Head title="Empresas" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Empresas</h2>
                <Link
                    :href="route('admin.companies.create')"
                    class="rounded-md bg-talents-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-talents-700"
                >
                    Nova empresa
                </Link>
            </div>
        </template>

        <form class="mb-6 flex gap-2" @submit.prevent="submit">
            <TextInput v-model="form.search" class="w-full max-w-md" placeholder="Buscar por nome ou CNPJ" />
            <PrimaryButton type="submit">Filtrar</PrimaryButton>
        </form>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 text-sm text-gray-900">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Nome</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">CNPJ</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Segmento</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-700">Ativa</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="c in companies.data" :key="c.id">
                        <td class="px-4 py-3">{{ c.name }}</td>
                        <td class="px-4 py-3">{{ c.cnpj || '—' }}</td>
                        <td class="px-4 py-3">{{ c.segment || '—' }}</td>
                        <td class="px-4 py-3">{{ c.is_active ? 'Sim' : 'Não' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('admin.companies.show', c.id)" class="font-medium text-talents-700 hover:underline">Ver</Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
