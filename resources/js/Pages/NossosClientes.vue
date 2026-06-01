<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingLayout from '@/Components/Landing/LandingLayout.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const title = 'Nossos Clientes — Talents Gestão de Pessoas';
const description =
    'Empresas que confiam na Talents para transformar sua gestão de pessoas. Conheça cases, depoimentos e resultados de consultoria estratégica.';

const clients = [
    { name: 'Fortex', logo: '/images/clientes/fortex.png' },
    { name: 'Mais Mais', logo: '/images/clientes/maismais.png' },
    { name: 'Multibor', logo: '/images/clientes/multibor.png' },
    { name: 'Pasqualino', logo: '/images/clientes/pasqualino.png' },
    { name: 'PASSEG', logo: '/images/clientes/passeg.png' },
    { name: 'Ramep', logo: '/images/clientes/ramep.jpg' },
    { name: 'SOEM', logo: '/images/clientes/soem.png' },
    { name: 'Wizzard', logo: '/images/clientes/wizzard.png' },
];

const testimonials = [
    {
        quote:
            'A Talents trouxe clareza comportamental para nossa liderança e ajudou a alinhar equipes com mais consciência estratégica.',
        author: 'Diretora de RH',
        company: 'Empresa do setor industrial',
    },
    {
        quote:
            'Mais do que diagnóstico, encontramos direcionamento prático para fortalecer cultura, comunicação e engajamento.',
        author: 'Gestor de Pessoas',
        company: 'Empresa de serviços',
    },
    {
        quote:
            'A metodologia da Talents conectou comportamento humano com estratégia — exatamente o que precisávamos.',
        author: 'CEO',
        company: 'Empresa em transformação',
    },
];

const cases = [
    {
        title: 'Fortalecimento de liderança',
        description:
            'Programa de desenvolvimento comportamental para líderes, com foco em comunicação assertiva e gestão de equipes.',
        result: 'Maior clareza relacional e redução de conflitos internos.',
    },
    {
        title: 'Cultura e engajamento',
        description:
            'Diagnóstico estratégico e mapeamento comportamental para alinhar cultura organizacional à estratégia da empresa.',
        result: 'Equipes mais conectadas ao propósito e ao desenvolvimento.',
    },
    {
        title: 'Saúde organizacional e NR-1',
        description:
            'Apoio na gestão de riscos psicossociais com método, escuta do time e plano de ação estruturado.',
        result: 'Mais conformidade e ambiente emocionalmente mais saudável.',
    },
];

const breadcrumbJsonLd = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: typeof window !== 'undefined' ? window.location.origin : '' },
            { '@type': 'ListItem', position: 2, name: 'Nossos Clientes' },
        ],
    }),
);
</script>

<template>
    <LandingLayout
        :title="title"
        :description="description"
        :can-login="canLogin"
        :can-register="canRegister"
    >
        <template #head>
            <component :is="'script'" type="application/ld+json" head-key="jsonld-breadcrumb">
                {{ breadcrumbJsonLd }}
            </component>
        </template>

        <section class="mx-auto max-w-6xl px-4 py-16 md:py-20">
            <p class="text-sm font-semibold uppercase tracking-widest text-talents-600">Prova social</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight text-slate-900 md:text-5xl">Nossos Clientes</h1>
            <p class="mt-6 max-w-3xl text-lg leading-relaxed text-slate-600">
                Empresas que confiam na Talents para transformar sua gestão de pessoas através de consultoria
                estratégica, comportamento e desenvolvimento humano.
            </p>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-center text-2xl font-bold text-slate-900 md:text-3xl">Empresas atendidas</h2>
                <div class="mx-auto mt-10 grid max-w-4xl grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    <div
                        v-for="client in clients"
                        :key="client.name"
                        class="group flex h-20 items-center justify-center rounded-2xl border border-slate-200/80 bg-white/90 px-3 shadow-sm transition duration-200 hover:-translate-y-0.5 hover:border-talents-300 hover:shadow-md"
                    >
                        <img
                            :src="client.logo"
                            :alt="`Logo ${client.name} — cliente Talents`"
                            class="max-h-10 w-auto object-contain transition duration-200 group-hover:scale-105"
                        />
                    </div>
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-center text-2xl font-bold text-slate-900 md:text-3xl">Depoimentos</h2>
                <p class="mx-auto mt-3 max-w-2xl text-center text-sm text-slate-500">
                    Conteúdo placeholder — substituir por depoimentos reais quando disponíveis.
                </p>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <article
                        v-for="item in testimonials"
                        :key="item.author"
                        class="surface-card-soft flex flex-col p-6"
                    >
                        <p class="flex-1 text-sm leading-relaxed text-slate-600">"{{ item.quote }}"</p>
                        <div class="mt-4 border-t border-slate-200/60 pt-4">
                            <p class="text-sm font-semibold text-slate-900">{{ item.author }}</p>
                            <p class="text-xs text-slate-500">{{ item.company }}</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-center text-2xl font-bold text-slate-900 md:text-3xl">Cases de transformação</h2>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    <article v-for="item in cases" :key="item.title" class="surface-card-soft p-6">
                        <h3 class="text-lg font-semibold text-slate-900">{{ item.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ item.description }}</p>
                        <p class="mt-4 text-sm font-medium text-talents-700">{{ item.result }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="border-t border-white/30 bg-white/30 py-16 backdrop-blur-sm">
            <div class="mx-auto max-w-3xl px-4 text-center">
                <h2 class="text-2xl font-bold text-slate-900 md:text-3xl">
                    Faça parte dessa rede de empresas que transformam pessoas em estratégia.
                </h2>
                <Link :href="route('landing.contato')" class="btn-primary mt-8"> Falar com Especialista </Link>
            </div>
        </section>
    </LandingLayout>
</template>
