<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({ plans: Array });

const form = useForm({
    name: '',
    legal_name: '',
    cnpj: '',
    segment: '',
    employee_count_estimate: null,
    plan_id: null,
    is_active: true,
});

const submit = () => {
    form.post(route('admin.companies.store'));
};
</script>

<template>
    <Head title="Nova empresa" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Nova empresa</h2>
        </template>

        <form class="max-w-xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 text-gray-900 shadow-sm" @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Nome fantasia" />
                <TextInput id="name" v-model="form.name" class="mt-1 block w-full" required />
                <InputError class="mt-2" :message="form.errors.name" />
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
                <InputLabel for="employee_count_estimate" value="Qtd. colaboradores (estimativa)" />
                <TextInput id="employee_count_estimate" type="number" v-model="form.employee_count_estimate" class="mt-1 block w-full" />
            </div>
            <div>
                <InputLabel for="plan_id" value="Plano inicial (opcional)" />
                <select id="plan_id" v-model="form.plan_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-talents-500 focus:ring-talents-500">
                    <option :value="null">—</option>
                    <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>
            <PrimaryButton :disabled="form.processing">Salvar</PrimaryButton>
        </form>
    </AdminLayout>
</template>
