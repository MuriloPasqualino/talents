<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingLayout from '@/Components/Landing/LandingLayout.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const title = 'Sobre a Talents — Consultoria Estratégica em Gestão de Pessoas';
const description =
    'Conheça a Talents: consultoria estratégica em gestão de pessoas com metodologias próprias, foco em comportamento, liderança, cultura organizacional e transformação consciente.';

const values = [
    { title: 'Clareza', description: 'Traduzimos comportamento humano em direcionamento estratégico compreensível.' },
    { title: 'Consciência', description: 'Acreditamos que toda transformação começa pelo autoconhecimento.' },
    { title: 'Estratégia', description: 'Comportamento também é estratégia — pessoas alinhadas ao crescimento.' },
    { title: 'Acolhimento', description: 'Consultoria premium, humana e próxima, sem linguagem fria de RH operacional.' },
];

const methodologies = [
    {
        title: 'Metamorfose Empresarial™',
        description:
            'Metodologia criada para transformar empresas através das pessoas — com diagnóstico estratégico, mapeamento comportamental, voz do time e imersão de perfis.',
        link: 'landing.empresas',
    },
    {
        title: 'Metamorfose Pessoal™',
        description:
            'Jornada de autoconhecimento e direcionamento para profissionais em diferentes fases da vida — mapeamento, análise de perfil e clareza estratégica.',
        link: 'landing.pessoas',
    },
];

const breadcrumbJsonLd = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: typeof window !== 'undefined' ? window.location.origin : '' },
            { '@type': 'ListItem', position: 2, name: 'Sobre' },
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
            <p class="text-sm font-semibold uppercase tracking-widest text-talents-600">Sobre nós</p>
            <h1 class="mt-4 text-4xl font-bold leading-tight text-slate-900 md:text-5xl">
                Consultoria estratégica em gestão de pessoas
            </h1>
            <p class="mt-6 max-w-3xl text-lg leading-relaxed text-slate-600">
                A Talents nasceu para ajudar empresas e profissionais a compreender comportamentos, fortalecer
                lideranças e transformar relações através do desenvolvimento humano e do direcionamento estratégico.
            </p>
            <p class="mt-4 max-w-3xl text-lg leading-relaxed text-slate-600">
                Não somos RH operacional, recrutamento isolado ou treinamentos genéricos. Somos consultoria com visão
                estratégica sobre comportamento, liderança, cultura organizacional e transformação consciente.
            </p>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <div class="grid gap-6 md:grid-cols-3">
                    <article class="surface-card-soft p-8">
                        <p class="text-sm font-bold uppercase tracking-wide text-talents-600">Missão</p>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Fortalecer empresas através das pessoas — transformando comportamento, liderança e cultura
                            em estratégias de crescimento sustentável.
                        </p>
                    </article>
                    <article class="surface-card-soft p-8">
                        <p class="text-sm font-bold uppercase tracking-wide text-talents-600">Visão</p>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Ser referência em consultoria estratégica em gestão de pessoas, reconhecida pela clareza,
                            metodologia própria e autoridade no mercado.
                        </p>
                    </article>
                    <article class="surface-card-soft p-8">
                        <p class="text-sm font-bold uppercase tracking-wide text-talents-600">Propósito</p>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">
                            Mais clareza. Mais consciência. Mais resultado — para pessoas e organizações que buscam
                            transformação real.
                        </p>
                    </article>
                </div>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-2xl font-bold text-slate-900 md:text-3xl">Nossos valores</h2>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="value in values" :key="value.title" class="surface-card-soft p-6">
                        <h3 class="font-semibold text-slate-900">{{ value.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ value.description }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-2xl font-bold text-slate-900 md:text-3xl">Metodologias próprias</h2>
                <p class="mt-3 max-w-3xl text-slate-600">
                    Desenvolvemos metodologias exclusivas para apoiar empresas e profissionais em jornadas de
                    transformação consciente e estratégica.
                </p>
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <article v-for="method in methodologies" :key="method.title" class="surface-card-soft p-8">
                        <h3 class="text-xl font-semibold text-slate-900">{{ method.title }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ method.description }}</p>
                        <Link
                            :href="route(method.link)"
                            class="mt-4 inline-block text-sm font-semibold text-talents-700 hover:underline"
                        >
                            Saiba mais →
                        </Link>
                    </article>
                </div>
            </div>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <div class="surface-card-dark p-8 md:p-12 text-center">
                    <p class="text-2xl font-bold md:text-3xl">Comportamento também é estratégia.</p>
                    <p class="mx-auto mt-4 max-w-2xl text-talents-100/90">
                        Transformamos pessoas em estratégias de crescimento — com sofisticação, clareza e autoridade.
                    </p>
                    <Link :href="route('landing.contato')" class="btn-secondary mt-8 border-white/30 bg-white/10 text-white hover:bg-white/20">
                        Falar com Especialista
                    </Link>
                </div>
            </div>
        </section>
    </LandingLayout>
</template>
