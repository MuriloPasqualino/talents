<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    provider: props.settings.provider,
    model: props.settings.model,
    api_key: '',
    is_enabled: props.settings.is_enabled,
    max_tokens: props.settings.max_tokens,
    temperature: props.settings.temperature,
});

const submit = () => {
    form.put(route('admin.ai-settings.update'));
};

const testConnection = () => {
    router.post(
        route('admin.ai-settings.test'),
        {
            provider: form.provider,
            model: form.model,
            api_key: form.api_key || null,
            max_tokens: form.max_tokens,
            temperature: form.temperature,
        },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Configuração de IA" />

    <AdminLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-900">Configuração de IA (NR-1)</h2>
            <p class="mt-1 text-sm text-gray-600">
                Chave e modelo centralizados na Talents. Empresas clientes usam a mesma configuração quando a IA estiver habilitada.
            </p>
        </template>

        <div
            v-if="$page.props.flash?.success"
            class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900"
        >
            {{ $page.props.flash.success }}
        </div>
        <div
            v-if="$page.props.flash?.error"
            class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900"
        >
            {{ $page.props.flash.error }}
        </div>

        <form class="max-w-2xl space-y-4 rounded-xl border border-gray-200 bg-white p-6 text-gray-900 shadow-sm" @submit.prevent="submit">
            <div>
                <InputLabel for="provider" value="Provedor" />
                <select
                    id="provider"
                    v-model="form.provider"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                >
                    <option value="openai">OpenAI</option>
                    <option value="anthropic">Anthropic</option>
                </select>
            </div>
            <div>
                <InputLabel for="model" value="Modelo" />
                <TextInput id="model" v-model="form.model" class="mt-1 block w-full" required placeholder="ex.: gpt-4o-mini ou claude-3-5-haiku-20241022" />
            </div>
            <div>
                <InputLabel for="api_key" value="Chave da API" />
                <TextInput
                    id="api_key"
                    v-model="form.api_key"
                    type="password"
                    class="mt-1 block w-full"
                    autocomplete="off"
                    :placeholder="settings.api_key_set ? 'Deixe em branco para manter a chave atual' : 'Cole a chave secreta'"
                />
                <p v-if="settings.api_key_set" class="mt-1 text-xs text-gray-500">Uma chave já está salva (criptografada).</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="max_tokens" value="Máx. tokens (resposta)" />
                    <TextInput id="max_tokens" v-model="form.max_tokens" type="number" class="mt-1 block w-full" required min="100" max="16000" />
                </div>
                <div>
                    <InputLabel for="temperature" value="Temperatura (0–2)" />
                    <TextInput
                        id="temperature"
                        v-model="form.temperature"
                        type="number"
                        step="0.01"
                        min="0"
                        max="2"
                        class="mt-1 block w-full"
                        required
                    />
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input
                    v-model="form.is_enabled"
                    type="checkbox"
                    class="rounded border-gray-300 text-talents-600 focus:ring-talents-500"
                />
                IA habilitada globalmente para clientes
            </label>
            <div class="flex flex-wrap gap-3">
                <PrimaryButton :disabled="form.processing">Salvar</PrimaryButton>
                <SecondaryButton type="button" :disabled="form.processing" @click="testConnection">Testar conexão</SecondaryButton>
            </div>
        </form>
    </AdminLayout>
</template>
