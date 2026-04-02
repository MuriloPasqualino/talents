<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ company: Object, plans: Array });

const form = useForm({
    name: props.company.name,
    legal_name: props.company.legal_name ?? '',
    cnpj: props.company.cnpj ?? '',
    segment: props.company.segment ?? '',
    employee_count_estimate: props.company.employee_count_estimate,
    is_active: props.company.is_active,
});

const submit = () => {
    form.put(route('admin.companies.update', props.company.id));
};
</script>

<template>
    <Head title="Editar empresa" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Editar empresa</h2>
        </template>

        <form class="max-w-xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 text-gray-900 shadow-sm" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nome" />
                <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
            </div>
            <div>
                <InputLabel for="legal_name" value="Razão social" />
                <TextInput id="legal_name" v-model="form.legal_name" class="mt-1 block w-full" />
            </div>
            <div>
                <InputLabel for="cnpj" value="CNPJ" />
                <TextInput id="cnpj" v-model="form.cnpj" class="mt-1 block w-full" />
            </div>
            <div>
                <InputLabel for="segment" value="Segmento" />
                <TextInput id="segment" v-model="form.segment" class="mt-1 block w-full" />
            </div>
            <div>
                <InputLabel for="employee_count_estimate" value="Qtd. colaboradores" />
                <TextInput id="employee_count_estimate" type="number" v-model="form.employee_count_estimate" class="mt-1 block w-full" />
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input v-model="form.is_active" type="checkbox" class="rounded border-gray-300 text-talents-600 focus:ring-talents-500" />
                Ativa
            </label>
            <PrimaryButton :disabled="form.processing">Atualizar</PrimaryButton>
        </form>
    </AdminLayout>
</template>
