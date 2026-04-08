<script setup>
import RhidResponsePanel from '@/Components/Rhid/RhidResponsePanel.vue';
import ClientLayout from '@/Layouts/ClientLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref, watch } from 'vue';
import {
    extractListItems,
    monthRangeHtmlDates,
    todayHtmlDate,
    toRhidYmd,
    toRhidYmdHm,
} from '@/utils/rhidDate';

const page = usePage();

const props = defineProps({
    configured: { type: Boolean, required: true },
    recentAudits: { type: Array, default: () => [] },
});

const tab = ref('overview');
const err = ref(null);
const loading = ref(false);
/** Ultima resposta de API para o painel legivel (exceto marcacoes) */
const responsePanelData = ref(null);

const lastPunches = ref([]);
const justificationTypes = ref(null);
const devices = ref(null);

const { first: monthFirst, last: monthLast } = monthRangeHtmlDates();

const jIniDate = ref(monthFirst);
const jFimDate = ref(monthLast);
const jPage = ref(0);
const jMax = ref(50);

const jIni = computed(() => toRhidYmd(jIniDate.value));
const jFim = computed(() => toRhidYmd(jFimDate.value));

const bankDateHtml = ref(todayHtmlDate());

const reportGuid = ref('');
const reportPercent = ref(null);
const reportIniDate = ref(monthFirst);
const reportFimDate = ref(monthLast);
const reportFormato = ref('PDF');
const reportNome = ref('espelho');
const reportJsonOverride = ref('');

const reportIni = computed(() => toRhidYmd(reportIniDate.value));
const reportFim = computed(() => toRhidYmd(reportFimDate.value));

const afdTipo = ref('afd');
const afdIniDate = ref(monthFirst);
const afdFimDate = ref(monthLast);
const afdBody = ref('[99000001]');

const afdIni = computed(() => toRhidYmd(afdIniDate.value));
const afdFim = computed(() => toRhidYmd(afdFimDate.value));

const massJ = ref({
    idJustificationType: null,
    justificativa: 'Feriado',
    minutesDiurno: 0,
    minutesNoturno: 0,
    selectedIdPerson: '',
});

const massJDateStart = ref(todayHtmlDate());
const massJTimeStart = ref('00:00');
const massJDateEnd = ref(todayHtmlDate());
const massJTimeEnd = ref('23:59');

const shiftIdPerson = ref(1);
const shiftIdShift = ref(1);
const shiftStartDate = ref(todayHtmlDate());
const shiftStartTime = ref('00:00');
const shiftEndDate = ref('2099-12-31');
const shiftEndTime = ref('00:00');
const shiftUseAdvanced = ref(false);
const shiftJson = ref(
    '[{"idPerson": 1, "idShift": 1, "startStr": "202403060000", "endStr": "209912310000"}]',
);

const isAdmin = computed(() => page.props.auth?.user?.role === 'company_admin');

const justificationTypeOptions = computed(() => {
    const items = extractListItems(justificationTypes.value);
    return items
        .map((row) => {
            const id = row.id ?? row.idJustificationType;
            const name =
                row.name ?? row.descricao ?? row.description ?? row.str ?? (id != null ? `Tipo ${id}` : '');
            if (id == null) {
                return null;
            }
            return { id, label: `${name} (#${id})` };
        })
        .filter(Boolean);
});

const clearErr = () => {
    err.value = null;
};

const handleError = (e) => {
    err.value = e.response?.data?.message || e.message || 'Erro na requisicao';
};

const prefetchJustificationTypesForMassForm = async () => {
    if (!props.configured) {
        return;
    }
    try {
        const { data } = await axios.get(route('client.rhid.api.justification-types'));
        justificationTypes.value = data;
        const opts = extractListItems(data);
        const firstId = opts[0]?.id ?? opts[0]?.idJustificationType;
        if (firstId != null && massJ.value.idJustificationType == null) {
            massJ.value.idJustificationType = firstId;
        }
    } catch {
        /* formulario continua com campo manual se falhar */
    }
};

watch(
    () => tab.value,
    (t) => {
        if (t === 'justifications') {
            prefetchJustificationTypesForMassForm();
        }
    },
);

const loadLastPunches = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.get(route('client.rhid.api.last-punches'));
        lastPunches.value = Array.isArray(data) ? data : [];
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const loadJustificationTypes = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.get(route('client.rhid.api.justification-types'));
        justificationTypes.value = data;
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const loadAlertTypes = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.get(route('client.rhid.api.alert-types'));
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const loadJustifications = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.post(route('client.rhid.api.justifications.list'), {
            ini: jIni.value,
            fim: jFim.value,
            page: jPage.value,
            maxSize: jMax.value,
        });
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const loadBankHours = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const dateParam = toRhidYmd(bankDateHtml.value) || bankDateHtml.value;
        const { data } = await axios.get(route('client.rhid.api.person-bank-hours'), {
            params: { date: dateParam },
        });
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const loadDevices = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.get(route('client.rhid.api.devices.index'));
        devices.value = data;
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const buildReportPayload = () => {
    const base = {
        formatoSaida: reportFormato.value,
        ini: reportIni.value,
        fim: reportFim.value,
        relatorio: reportNome.value,
        destinoRelatorio: 'DOWNLOAD',
        ordenacao: 'Person',
        pdfCartaoPontoParameters: {
            fontSizeTitle: 12,
            fontSizeData: 8,
            fontSizeHeader: 9,
            fontSizeHeaderSmall: 8,
            fontSizeFooter: 8,
            fontName: 'Helvetica',
            listIdStr: [],
            listCompanyStr: [],
            listDepartmentStr: [],
            listPersonRoleStr: [],
            listCostCenterStr: [],
            listShiftStr: [],
        },
    };
    if (reportNome.value === 'espelho') {
        base.listColumns = ['TODAS_MARCACOES', 'ENTRADAS_SAIDAS'];
        base.listPropertyStr = ['TODAS_MARCACOES', 'ENTRADAS_SAIDAS'];
    } else {
        base.listColumns = ['strHorarioContratualSimples', 'horasTotalNaoExtra'];
        base.listPersonInfo = ['Person.name', 'Person.pis'];
    }
    return base;
};

const startReport = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    reportGuid.value = '';
    reportPercent.value = null;
    try {
        let body;
        const raw = reportJsonOverride.value.trim();
        if (raw) {
            body = JSON.parse(raw);
        } else {
            body = buildReportPayload();
        }
        const { data } = await axios.post(route('client.rhid.api.reports.start'), body);
        reportGuid.value = data.guid || '';
        responsePanelData.value = data;
    } catch (e) {
        if (e instanceof SyntaxError) {
            err.value = 'JSON invalido no campo avancado.';
        } else {
            handleError(e);
        }
    } finally {
        loading.value = false;
    }
};

const pollReportStatus = async () => {
    if (!reportGuid.value) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.get(route('client.rhid.api.reports.status'), {
            params: { guid: reportGuid.value },
        });
        reportPercent.value = data.percent ?? null;
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const downloadReport = () => {
    if (!reportGuid.value) {
        return;
    }
    const url =
        route('client.rhid.api.reports.download') +
        `?guid=${encodeURIComponent(reportGuid.value)}&format=${encodeURIComponent(reportFormato.value)}`;
    window.open(url, '_blank');
};

const exportAfd = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.post(
            route('client.rhid.api.afd.export'),
            {
                tipo: afdTipo.value,
                ini: afdIni.value,
                fim: afdFim.value,
                body: afdBody.value,
            },
            { responseType: 'blob' },
        );
        const blob = data;
        const ext = afdTipo.value === 'afd671' ? 'zip' : 'txt';
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = `afd-${afdIni.value}-${afdFim.value}.${ext}`;
        a.click();
        URL.revokeObjectURL(a.href);
        responsePanelData.value = { ok: true, mensagem: 'Arquivo AFD baixado com sucesso.' };
    } catch (e) {
        if (e.response?.data instanceof Blob) {
            const text = await e.response.data.text();
            err.value = text || 'Erro na exportacao AFD';
        } else {
            handleError(e);
        }
    } finally {
        loading.value = false;
    }
};

const submitMassJustification = async () => {
    if (!props.configured) {
        return;
    }
    if (massJ.value.idJustificationType == null) {
        err.value = 'Selecione o tipo de justificativa.';
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const ids = massJ.value.selectedIdPerson
            .split(/[\s,]+/)
            .map((s) => parseInt(s, 10))
            .filter((n) => !Number.isNaN(n));
        const inicio = toRhidYmdHm(massJDateStart.value, massJTimeStart.value);
        const fim = toRhidYmdHm(massJDateEnd.value, massJTimeEnd.value);
        const { data } = await axios.post(route('client.rhid.api.justifications.mass'), {
            idJustificationType: massJ.value.idJustificationType,
            justificativa: massJ.value.justificativa,
            inicio,
            fim,
            minutesDiurno: massJ.value.minutesDiurno,
            minutesNoturno: massJ.value.minutesNoturno,
            selectedIdPerson: ids,
        });
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const submitMassShift = async () => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        let items;
        if (shiftUseAdvanced.value) {
            items = JSON.parse(shiftJson.value);
        } else {
            items = [
                {
                    idPerson: Number(shiftIdPerson.value),
                    idShift: Number(shiftIdShift.value),
                    startStr: toRhidYmdHm(shiftStartDate.value, shiftStartTime.value),
                    endStr: toRhidYmdHm(shiftEndDate.value, shiftEndTime.value),
                },
            ];
        }
        const { data } = await axios.post(route('client.rhid.api.person-shift.mass'), items);
        responsePanelData.value = data;
    } catch (e) {
        if (e instanceof SyntaxError) {
            err.value = 'JSON invalido (horario em massa avancado).';
        } else {
            handleError(e);
        }
    } finally {
        loading.value = false;
    }
};

const forceResync = async () => {
    if (!props.configured) {
        return;
    }
    if (!window.confirm('Ressincronizar TODOS os funcionarios em TODOS os equipamentos?')) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.post(route('client.rhid.api.sync.force-all'));
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const enableIdCloud = async (id) => {
    if (!props.configured) {
        return;
    }
    loading.value = true;
    clearErr();
    try {
        const { data } = await axios.post(route('client.rhid.api.devices.id-cloud', id));
        responsePanelData.value = data;
    } catch (e) {
        handleError(e);
    } finally {
        loading.value = false;
    }
};

const tabs = [
    { id: 'overview', label: 'Visao geral' },
    { id: 'punches', label: 'Marcacoes' },
    { id: 'types', label: 'Tipos' },
    { id: 'justifications', label: 'Justificativas' },
    { id: 'bank', label: 'Banco de horas' },
    { id: 'reports', label: 'Relatorios' },
    { id: 'afd', label: 'AFD' },
    { id: 'devices', label: 'Equipamentos' },
    { id: 'audit', label: 'Auditoria' },
];
</script>

<template>
    <Head title="Compliance RHID" />

    <ClientLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <h2 class="text-xl font-semibold leading-tight text-talents-900">Compliance de ponto — RHID</h2>
                <Link
                    v-if="isAdmin"
                    :href="route('client.rhid.settings.edit')"
                    class="text-sm font-medium text-talents-700 hover:underline"
                >
                    Configuracao
                </Link>
            </div>
        </template>

        <div
            v-if="!configured"
            class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-900"
        >
            <p class="font-semibold">Integracao nao configurada</p>
            <p class="mt-1">Cadastre e-mail e senha da API RHID para usar este modulo.</p>
            <Link
                v-if="isAdmin"
                :href="route('client.rhid.settings.edit')"
                class="mt-3 inline-block text-sm font-bold text-talents-800 underline"
            >
                Abrir configuracoes
            </Link>
        </div>

        <div v-else class="space-y-6">
            <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-2">
                <button
                    v-for="t in tabs"
                    :key="t.id"
                    type="button"
                    class="rounded-md px-3 py-1.5 text-sm font-medium"
                    :class="
                        tab === t.id
                            ? 'bg-talents-700 text-white'
                            : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                    "
                    @click="tab = t.id"
                >
                    {{ t.label }}
                </button>
            </div>

            <p v-if="err" class="rounded-md bg-red-50 p-3 text-sm text-red-800">{{ err }}</p>
            <p v-if="loading" class="text-sm text-slate-500">Carregando...</p>

            <div v-show="tab === 'overview'" class="space-y-4 text-sm text-slate-700">
                <p>
                    Modulo de integracao com a API REST do RHID: justificativas, banco de horas, relatorios
                    assincronos (GUID), AFD, equipamentos e monitoramento.
                </p>
                <ul class="list-inside list-disc text-slate-600">
                    <li>Requisicoes sao auditadas (sem dados sensiveis completos nos logs).</li>
                    <li>Relatorios: inicie o processo, consulte o percentual e baixe quando estiver 100%.</li>
                    <li>AFD 671 retorna arquivo ZIP (tratado no download).</li>
                </ul>
            </div>

            <div v-show="tab === 'punches'" class="space-y-3">
                <PrimaryButton type="button" :disabled="loading" @click="loadLastPunches">Atualizar marcacoes</PrimaryButton>
                <div class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-2">ID</th>
                                <th class="p-2">Nome</th>
                                <th class="p-2">Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, i) in lastPunches" :key="i" class="border-t border-slate-100">
                                <td class="p-2">{{ row.id }}</td>
                                <td class="p-2">{{ row.nome }}</td>
                                <td class="p-2">{{ row.data }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-show="tab === 'types'" class="space-y-3">
                <div class="flex flex-wrap gap-2">
                    <PrimaryButton type="button" :disabled="loading" @click="loadJustificationTypes">
                        Tipos de justificativa
                    </PrimaryButton>
                    <PrimaryButton type="button" :disabled="loading" @click="loadAlertTypes">
                        Tipos de inconsistencia
                    </PrimaryButton>
                </div>
                <RhidResponsePanel
                    v-if="responsePanelData && tab === 'types'"
                    :data="responsePanelData"
                    title="Ultima resposta"
                />
            </div>

            <div v-show="tab === 'justifications'" class="space-y-4">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <InputLabel value="Data inicial" />
                        <input
                            v-model="jIniDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                        />
                    </div>
                    <div>
                        <InputLabel value="Data final" />
                        <input
                            v-model="jFimDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                        />
                    </div>
                    <div>
                        <InputLabel value="Pagina" />
                        <TextInput v-model.number="jPage" type="number" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <InputLabel value="Itens por pagina" />
                        <TextInput v-model.number="jMax" type="number" class="mt-1 block w-full" />
                    </div>
                </div>
                <p class="text-xs text-slate-500">Periodo enviado ao RHID: {{ jIni }} — {{ jFim }}</p>
                <PrimaryButton type="button" :disabled="loading" @click="loadJustifications">Listar</PrimaryButton>

                <div class="rounded-lg border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-talents-900">Justificativa em massa</h3>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <InputLabel value="Tipo de justificativa" />
                            <select
                                v-model.number="massJ.idJustificationType"
                                class="mt-1 w-full rounded-md border border-slate-300 text-sm"
                            >
                                <option v-if="justificationTypeOptions.length === 0" disabled value="">
                                    Carregue a aba ou aguarde tipos...
                                </option>
                                <option v-for="opt in justificationTypeOptions" :key="opt.id" :value="opt.id">
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="Texto da justificativa" />
                            <TextInput v-model="massJ.justificativa" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel value="Data inicial" />
                            <input
                                v-model="massJDateStart"
                                type="date"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Hora inicial" />
                            <input
                                v-model="massJTimeStart"
                                type="time"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Data final" />
                            <input
                                v-model="massJDateEnd"
                                type="date"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Hora final" />
                            <input
                                v-model="massJTimeEnd"
                                type="time"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div class="sm:col-span-2">
                            <InputLabel value="IDs dos funcionarios (separados por virgula)" />
                            <TextInput v-model="massJ.selectedIdPerson" class="mt-1 w-full" placeholder="1, 2, 3" />
                        </div>
                    </div>
                    <PrimaryButton type="button" class="mt-3" :disabled="loading" @click="submitMassJustification">
                        Enviar em massa
                    </PrimaryButton>
                </div>

                <div class="rounded-lg border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold text-talents-900">Horario em massa</h3>
                    <label class="mt-2 flex items-center gap-2 text-sm text-slate-700">
                        <input v-model="shiftUseAdvanced" type="checkbox" class="rounded border-slate-300" />
                        Modo avancado (JSON)
                    </label>
                    <div v-if="!shiftUseAdvanced" class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div>
                            <InputLabel value="ID funcionario" />
                            <TextInput v-model.number="shiftIdPerson" type="number" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel value="ID escala" />
                            <TextInput v-model.number="shiftIdShift" type="number" class="mt-1 w-full" />
                        </div>
                        <div>
                            <InputLabel value="Inicio (data)" />
                            <input
                                v-model="shiftStartDate"
                                type="date"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Inicio (hora)" />
                            <input
                                v-model="shiftStartTime"
                                type="time"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Fim (data)" />
                            <input
                                v-model="shiftEndDate"
                                type="date"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                        <div>
                            <InputLabel value="Fim (hora)" />
                            <input
                                v-model="shiftEndTime"
                                type="time"
                                class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                            />
                        </div>
                    </div>
                    <div v-else class="mt-2">
                        <InputLabel value="Array JSON" />
                        <textarea
                            v-model="shiftJson"
                            class="mt-2 w-full rounded-md border border-slate-300 font-mono text-xs"
                            rows="5"
                        />
                    </div>
                    <PrimaryButton type="button" class="mt-2" :disabled="loading" @click="submitMassShift">
                        Aplicar horarios
                    </PrimaryButton>
                </div>

                <RhidResponsePanel
                    v-if="responsePanelData && tab === 'justifications'"
                    :data="responsePanelData"
                    title="Ultima resposta"
                />
            </div>

            <div v-show="tab === 'bank'" class="space-y-3">
                <div class="max-w-xs">
                    <InputLabel value="Data de referencia" />
                    <input
                        v-model="bankDateHtml"
                        type="date"
                        class="mt-1 block w-full rounded-md border border-slate-300 text-sm shadow-sm"
                    />
                </div>
                <PrimaryButton type="button" :disabled="loading" @click="loadBankHours">Consultar banco de horas</PrimaryButton>
                <RhidResponsePanel
                    v-if="responsePanelData && tab === 'bank'"
                    :data="responsePanelData"
                    title="Resultado"
                />
            </div>

            <div v-show="tab === 'reports'" class="space-y-3">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <InputLabel value="Formato de saida" />
                        <select v-model="reportFormato" class="mt-1 w-full rounded-md border border-slate-300 text-sm">
                            <option value="PDF">PDF</option>
                            <option value="PDF2">PDF2</option>
                            <option value="CSV">CSV</option>
                            <option value="HTML">HTML</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Tipo de relatorio" />
                        <select v-model="reportNome" class="mt-1 w-full rounded-md border border-slate-300 text-sm">
                            <option value="espelho">Espelho de ponto</option>
                            <option value="cartao">Cartao de ponto</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Data inicial" />
                        <input
                            v-model="reportIniDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                        />
                    </div>
                    <div>
                        <InputLabel value="Data final" />
                        <input
                            v-model="reportFimDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                        />
                    </div>
                </div>
                <details class="rounded-md border border-slate-200 bg-slate-50 p-3 text-sm">
                    <summary class="cursor-pointer font-medium text-slate-800">JSON avancado (opcional)</summary>
                    <p class="mt-2 text-xs text-slate-600">Substitui os campos acima se preenchido.</p>
                    <textarea
                        v-model="reportJsonOverride"
                        class="mt-2 w-full rounded-md border border-slate-300 font-mono text-xs"
                        rows="5"
                        placeholder="Payload completo POST relatorio ponto"
                    />
                </details>
                <div class="flex flex-wrap gap-2">
                    <PrimaryButton type="button" :disabled="loading" @click="startReport">Iniciar relatorio</PrimaryButton>
                    <PrimaryButton type="button" :disabled="loading || !reportGuid" @click="pollReportStatus">
                        Atualizar status
                    </PrimaryButton>
                    <PrimaryButton type="button" :disabled="!reportGuid" @click="downloadReport">Download</PrimaryButton>
                </div>
                <p v-if="reportGuid" class="text-sm text-slate-600">
                    GUID: <code class="rounded bg-slate-100 px-1">{{ reportGuid }}</code>
                    <span v-if="reportPercent !== null" class="ml-2">Percentual: {{ reportPercent }}%</span>
                </p>
                <RhidResponsePanel
                    v-if="responsePanelData && tab === 'reports'"
                    :data="responsePanelData"
                    title="Status / resposta"
                />
            </div>

            <div v-show="tab === 'afd'" class="space-y-3">
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <InputLabel value="Tipo de arquivo" />
                        <select v-model="afdTipo" class="mt-1 w-full rounded-md border border-slate-300 text-sm">
                            <option value="afd">AFD (layout 1510)</option>
                            <option value="afd671">AFD 671 (ZIP)</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Data inicial" />
                        <input
                            v-model="afdIniDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                        />
                    </div>
                    <div>
                        <InputLabel value="Data final" />
                        <input
                            v-model="afdFimDate"
                            type="date"
                            class="mt-1 block w-full rounded-md border border-slate-300 text-sm"
                        />
                    </div>
                    <div>
                        <InputLabel value="Filtro interno (REP)" />
                        <TextInput v-model="afdBody" class="mt-1 w-full" placeholder='ex.: [99000001]' />
                    </div>
                </div>
                <details class="text-xs text-slate-600">
                    <summary class="cursor-pointer">O que e este filtro?</summary>
                    <p class="mt-1">Lista de numeros de fabricacao dos REP, em JSON. O padrao atende exportacao geral.</p>
                </details>
                <PrimaryButton type="button" :disabled="loading" @click="exportAfd">Exportar AFD</PrimaryButton>
                <RhidResponsePanel
                    v-if="responsePanelData && tab === 'afd'"
                    :data="responsePanelData"
                    title="Resultado"
                />
            </div>

            <div v-show="tab === 'devices'" class="space-y-3">
                <div class="flex flex-wrap gap-2">
                    <PrimaryButton type="button" :disabled="loading" @click="loadDevices">Listar equipamentos</PrimaryButton>
                    <button
                        type="button"
                        class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-medium text-red-800 hover:bg-red-50"
                        :disabled="loading"
                        @click="forceResync"
                    >
                        Ressincronizar todos
                    </button>
                </div>
                <div v-if="devices?.data?.length" class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-2">ID</th>
                                <th class="p-2">Nome</th>
                                <th class="p-2">Host</th>
                                <th class="p-2">Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="d in devices.data" :key="d.id" class="border-t border-slate-100">
                                <td class="p-2">{{ d.id }}</td>
                                <td class="p-2">{{ d.name }}</td>
                                <td class="p-2">{{ d.host }}</td>
                                <td class="p-2">
                                    <button
                                        type="button"
                                        class="text-xs font-semibold text-talents-700 hover:underline"
                                        @click="enableIdCloud(d.id)"
                                    >
                                        iDCloud
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <RhidResponsePanel
                    v-if="responsePanelData && tab === 'devices'"
                    :data="responsePanelData"
                    title="Ultima resposta"
                />
            </div>

            <div v-show="tab === 'audit'" class="space-y-3">
                <p class="text-sm text-slate-600">Ultimas acoes registradas nesta empresa (via Talents).</p>
                <div class="overflow-x-auto rounded border border-slate-200">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="p-2">Quando</th>
                                <th class="p-2">Usuario</th>
                                <th class="p-2">Acao</th>
                                <th class="p-2">HTTP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="a in recentAudits" :key="a.id" class="border-t border-slate-100">
                                <td class="p-2 whitespace-nowrap">{{ a.created_at }}</td>
                                <td class="p-2">{{ a.user?.name || '—' }}</td>
                                <td class="p-2 font-mono text-xs">{{ a.action }}</td>
                                <td class="p-2">{{ a.http_status }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </ClientLayout>
</template>
