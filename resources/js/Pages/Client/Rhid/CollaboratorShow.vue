<script setup>
import ClientLayout from '@/Layouts/ClientLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, ref } from 'vue';
import {
    formatRhidBankBalanceDisplay,
    formatRhidDotNetDate,
    pickRhidPersonDisplayName,
    todayHtmlDate,
    toRhidYmd,
} from '@/utils/rhidDate';

const props = defineProps({
    configured: { type: Boolean, required: true },
    personId: { type: Number, required: true },
});

const loading = ref(false);
const err = ref(null);
const detail = ref(null);
const bankDateHtml = ref(todayHtmlDate());
const bankPack = ref(null);

const displayName = computed(() => pickRhidPersonDisplayName(detail.value ?? {}));
const bankRow = computed(() => {
    const rows = bankPack.value?.rows;
    return Array.isArray(rows) && rows[0] ? rows[0] : null;
});

const deptLabel = computed(() => {
    const d = detail.value;
    if (!d || typeof d !== 'object') {
        return '—';
    }
    if (d.departmentName && String(d.departmentName).trim()) {
        return String(d.departmentName).trim();
    }
    return d.idDepartment != null ? `#${d.idDepartment}` : '—';
});

const cargoLabel = computed(() => {
    const d = detail.value;
    if (!d || typeof d !== 'object') {
        return '—';
    }
    if (d.roleName && String(d.roleName).trim()) {
        return String(d.roleName).trim();
    }
    return d.idPersonRole != null ? `#${d.idPersonRole}` : '—';
});

const admissaoLabel = computed(() => {
    const d = detail.value;
    if (!d) {
        return '—';
    }
    const s = d.admissionDateStr && String(d.admissionDateStr).trim();
    if (s) {
        return s;
    }
    return formatRhidDotNetDate(d.admissionDate) || '—';
});

const clearErr = () => {
    err.value = null;
};

const handleError = (e) => {
    err.value = e.response?.data?.message || e.message || 'Erro na requisicao';
};

const loadDetail = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    detail.value = null;
    try {
        const { data } = await axios.get(route('client.rhid.api.people.show', props.personId));
        detail.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const loadBank = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    bankPack.value = null;
    try {
        const dateParam = toRhidYmd(bankDateHtml.value) || bankDateHtml.value;
        const { data } = await axios.get(route('client.rhid.api.person-bank-hours'), {
            params: {
                date: dateParam,
                people: [props.personId],
            },
        });
        bankPack.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

onMounted(async () => {
    if (!props.configured) {
        return;
    }
    await loadDetail();
    await loadBank();
});
</script>

<template>
    <Head :title="`RHID — ${displayName !== '—' ? displayName : 'Colaborador'}`" />

    <ClientLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <Link
                        :href="route('client.rhid.compliance.index')"
                        class="mb-1 inline-block text-sm text-talents-700 hover:underline"
                    >
                        Compliance RHID
                    </Link>
                    <h2 class="text-xl font-semibold leading-tight text-talents-900">
                        Colaborador — RHID
                    </h2>
                </div>
            </div>
        </template>

        <div
            v-if="!configured"
            class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900"
        >
            <p class="font-semibold">Integracao nao configurada</p>
            <p class="mt-1">Cadastre e-mail e senha da API RHID nas configuracoes.</p>
        </div>

        <div v-else class="space-y-6">
            <p v-if="err" class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ err }}</p>
            <p v-if="loading && !detail" class="text-sm text-slate-500">Carregando cadastro...</p>

            <div
                v-if="detail"
                class="grid gap-4 md:grid-cols-2"
            >
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">Identificacao</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Nome</dt>
                            <dd class="text-right font-medium text-slate-900">{{ displayName }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">ID RHID</dt>
                            <dd class="font-mono text-xs text-slate-800">{{ detail.id ?? personId }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Matricula / PIS</dt>
                            <dd class="text-slate-800">
                                {{ detail.registration ?? detail.matricula ?? detail.pis ?? '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">CPF</dt>
                            <dd class="text-slate-800">{{ detail.cpf ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 pb-1">
                            <dt class="text-slate-500">E-mail</dt>
                            <dd class="max-w-[14rem] truncate text-slate-800" :title="detail.email || ''">
                                {{ detail.email || '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Telefone</dt>
                            <dd class="text-slate-800">{{ detail.phone || '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-slate-800">Vinculo</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Empresa</dt>
                            <dd class="max-w-[14rem] truncate text-right text-slate-800" :title="detail.companyTradingName || detail.companyName || ''">
                                {{ detail.companyTradingName || detail.companyName || '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Departamento</dt>
                            <dd class="text-right text-slate-800">{{ deptLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Cargo</dt>
                            <dd class="text-right text-slate-800">{{ cargoLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Centro de custo</dt>
                            <dd class="text-right text-slate-800">
                                {{ detail.costCenterName || (detail.idCostCenter != null ? `#${detail.idCostCenter}` : '—') }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Admissao</dt>
                            <dd class="text-slate-800">{{ admissaoLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500">Status</dt>
                            <dd class="text-slate-800">{{ detail.statusStr ?? detail.status ?? '—' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div v-if="detail" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Banco de horas (referencia)</h3>
                <div class="mb-4 flex max-w-md flex-wrap items-end gap-3">
                    <div>
                        <InputLabel value="Data de referencia" />
                        <input
                            v-model="bankDateHtml"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm shadow-sm"
                        />
                    </div>
                    <PrimaryButton type="button" :disabled="loading" @click="loadBank">Consultar saldo</PrimaryButton>
                </div>
                <p v-if="bankPack?.source" class="mb-2 text-xs text-slate-500">
                    Fonte: {{ bankPack.source }} · Data: {{ bankPack.date }}
                </p>
                <p v-if="bankRow" class="text-lg font-semibold tabular-nums text-slate-900">
                    {{ formatRhidBankBalanceDisplay(bankRow) }}
                </p>
                <p v-else-if="bankPack && !loading" class="text-sm text-slate-500">Nenhum retorno de saldo para esta data.</p>
            </div>
        </div>
    </ClientLayout>
</template>
