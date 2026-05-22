<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    item: Object,
    companies: Array,
    kinds: Array,
    recurrences: Array,
});

const occursOn =
    typeof props.item.occurs_on === 'string'
        ? props.item.occurs_on.slice(0, 10)
        : props.item.occurs_on;

const recurrenceEndsOn = props.item.recurrence_ends_on
    ? String(props.item.recurrence_ends_on).slice(0, 10)
    : '';

const form = useForm({
    title: props.item.title,
    description: props.item.description ?? '',
    kind: props.item.kind,
    occurs_on: occursOn,
    recurrence: props.item.recurrence ?? '',
    recurrence_ends_on: recurrenceEndsOn,
    company_id: props.item.company_id ? String(props.item.company_id) : '',
    attachment: null,
    remove_attachment: false,
});

const showRecurrenceEnd = computed(() => Boolean(form.recurrence));

const submit = () => {
    form.transform((data) => ({
        ...data,
        company_id: data.company_id || null,
        recurrence: data.recurrence || null,
        recurrence_ends_on: data.recurrence ? data.recurrence_ends_on || null : null,
    })).put(route('admin.strategic-calendar.update', props.item.id), {
        forceFormData: true,
    });
};

const onFileChange = (e) => {
    form.attachment = e.target.files?.[0] ?? null;
    if (form.attachment) {
        form.remove_attachment = false;
    }
};
</script>

<template>
    <Head title="Editar item — Calendário estratégico" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.strategic-calendar.index')"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900"
                >
                    ← Voltar
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-900">Editar evento ou rito</h2>
            </div>
        </template>

        <form class="surface-card max-w-2xl space-y-4 p-6 text-slate-900" @submit.prevent="submit">
            <div>
                <InputLabel for="title" value="Nome" />
                <TextInput id="title" v-model="form.title" class="mt-1 block w-full" required />
            </div>
            <div>
                <InputLabel for="kind" value="Tipo" />
                <select
                    id="kind"
                    v-model="form.kind"
                    class="mt-1 block w-full rounded-md border border-gray-300 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                >
                    <option v-for="k in kinds" :key="k.value" :value="k.value">{{ k.label }}</option>
                </select>
            </div>
            <div>
                <InputLabel for="occurs_on" value="Data inicial" />
                <TextInput id="occurs_on" v-model="form.occurs_on" type="date" class="mt-1 block w-full max-w-[12rem]" required />
            </div>
            <div>
                <InputLabel for="recurrence" value="Repetição" />
                <select
                    id="recurrence"
                    v-model="form.recurrence"
                    class="mt-1 block w-full rounded-md border border-gray-300 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                >
                    <option value="">Não se repete</option>
                    <option v-for="r in recurrences" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>
            </div>
            <div v-if="showRecurrenceEnd">
                <InputLabel for="recurrence_ends_on" value="Repetir até (opcional)" />
                <TextInput
                    id="recurrence_ends_on"
                    v-model="form.recurrence_ends_on"
                    type="date"
                    class="mt-1 block w-full max-w-[12rem]"
                />
            </div>
            <div>
                <InputLabel for="description" value="Como fazer (orientações)" />
                <textarea
                    id="description"
                    v-model="form.description"
                    rows="6"
                    class="mt-1 block w-full rounded-md border border-gray-300 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                />
            </div>
            <div>
                <InputLabel for="attachment" value="Anexo" />
                <div v-if="item.has_attachment && !form.remove_attachment" class="mb-2 flex flex-wrap items-center gap-2 text-sm">
                    <a
                        :href="item.attachment_url"
                        class="font-medium text-talents-700 hover:underline"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        {{ item.attachment_original_name || 'Baixar anexo atual' }}
                    </a>
                    <button
                        type="button"
                        class="text-red-600 hover:underline"
                        @click="form.remove_attachment = true"
                    >
                        Remover
                    </button>
                </div>
                <input
                    id="attachment"
                    type="file"
                    class="mt-1 block w-full text-sm text-slate-600 file:mr-4 file:rounded-md file:border-0 file:bg-talents-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-talents-700"
                    @change="onFileChange"
                />
                <p class="mt-0.5 text-xs text-gray-500">Envie um novo arquivo para substituir o anexo atual.</p>
            </div>
            <div>
                <InputLabel for="company_id" value="Empresa (opcional)" />
                <p class="mt-0.5 text-xs text-gray-500">Em branco = todas as empresas com o módulo habilitado.</p>
                <select
                    id="company_id"
                    v-model="form.company_id"
                    class="mt-1 block w-full rounded-md border border-gray-300 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                >
                    <option value="">Todas</option>
                    <option v-for="c in companies" :key="c.id" :value="String(c.id)">{{ c.name }}</option>
                </select>
            </div>
            <PrimaryButton :disabled="form.processing">Atualizar</PrimaryButton>
        </form>
    </AdminLayout>
</template>
