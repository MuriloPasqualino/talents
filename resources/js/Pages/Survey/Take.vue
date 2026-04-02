<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    survey: Object,
    likertLabels: Object,
    agreementLabels: Object,
    ageRanges: Object,
    tenureRanges: Object,
});

const labelsForQuestion = (q) => {
    if (q.response_scale === 'agreement' && props.agreementLabels) {
        return props.agreementLabels;
    }
    return props.likertLabels;
};

const buildAnswers = () => {
    const o = {};
    for (const s of props.survey.template.sections) {
        for (const q of s.questions) {
            o[q.id] = null;
        }
    }
    return o;
};

const form = useForm({
    answers: buildAnswers(),
    department_id: null,
    age_range: null,
    tenure_range: null,
});

const submit = () => {
    form.post(route('survey.public.submit', props.survey.public_token));
};

const progress = () => {
    const values = Object.values(form.answers);
    const answered = values.filter((v) => v !== null && v !== '').length;
    return Math.round((answered / values.length) * 100) || 0;
};
</script>

<template>
    <Head :title="survey.title" />

    <div class="min-h-screen bg-gray-50 text-gray-900">
        <header class="border-b border-gray-200 bg-white px-4 py-4 shadow-sm">
            <div class="mx-auto flex max-w-3xl items-center gap-3">
                <img src="/images/logo.png" alt="Talents" class="h-10 w-auto" />
                <div>
                    <p class="text-xs uppercase tracking-widest text-talents-600">Pesquisa anônima NR-1</p>
                    <p class="text-lg font-semibold text-gray-900">{{ survey.title }}</p>
                </div>
            </div>
        </header>

        <div class="mx-auto max-w-3xl px-4 py-6">
            <p class="text-sm text-gray-600">
                Suas respostas são confidenciais e agregadas. Não coletamos identificação pessoal.
            </p>

            <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-gray-200">
                <div class="h-full bg-talents-500 transition-all" :style="{ width: progress() + '%' }" />
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ progress() }}% respondido</p>

            <form class="mt-8 space-y-10" @submit.prevent="submit">
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                    <h3 class="text-sm font-semibold text-talents-700">Dados opcionais (agregados)</h3>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div v-if="survey.company?.departments?.length">
                            <label class="text-xs text-gray-600">Setor</label>
                            <select
                                v-model="form.department_id"
                                class="mt-1 w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            >
                                <option :value="null">Prefiro não informar</option>
                                <option v-for="d in survey.company.departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Faixa etária</label>
                            <select
                                v-model="form.age_range"
                                class="mt-1 w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            >
                                <option :value="null">Prefiro não informar</option>
                                <option v-for="(label, key) in ageRanges" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs text-gray-600">Tempo na empresa</label>
                            <select
                                v-model="form.tenure_range"
                                class="mt-1 w-full rounded-md border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            >
                                <option :value="null">Prefiro não informar</option>
                                <option v-for="(label, key) in tenureRanges" :key="key" :value="key">{{ label }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <section
                    v-for="section in survey.template.sections"
                    :key="section.id"
                    class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
                >
                    <h2 class="text-lg font-semibold text-talents-700">{{ section.title }}</h2>
                    <p v-if="section.description" class="mt-2 text-sm text-gray-600">{{ section.description }}</p>

                    <div v-for="q in section.questions" :key="q.id" class="mt-6 border-t border-gray-200 pt-4 first:border-t-0 first:pt-0">
                        <p class="text-sm font-medium text-gray-900">{{ q.body }}</p>
                        <p v-if="q.response_scale === 'agreement'" class="mt-1 text-xs text-gray-500">Escala de concordância (1 a 5)</p>
                        <p v-else class="mt-1 text-xs text-gray-500">Escala de frequência (1 a 5)</p>
                        <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-5">
                            <label
                                v-for="val in [1, 2, 3, 4, 5]"
                                :key="val"
                                class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs hover:border-talents-400"
                            >
                                <input v-model="form.answers[q.id]" type="radio" class="text-talents-600 focus:ring-talents-500" :value="val" required />
                                <span>{{ labelsForQuestion(q)[val] }}</span>
                            </label>
                        </div>
                    </div>
                </section>

                <button
                    type="submit"
                    class="w-full rounded-xl bg-talents-600 py-3 text-sm font-bold text-white shadow-lg hover:bg-talents-700"
                    :disabled="form.processing"
                >
                    Enviar respostas
                </button>
            </form>
        </div>
    </div>
</template>
