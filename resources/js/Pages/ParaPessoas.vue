<script setup>
import {
    AcademicCapIcon,
    ArrowPathIcon,
    ChatBubbleLeftRightIcon,
    ClipboardDocumentCheckIcon,
    HeartIcon,
    LightBulbIcon,
    MapIcon,
    ShieldCheckIcon,
    SparklesIcon,
    UserIcon,
} from '@heroicons/vue/24/outline';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import LandingCardGrid from '@/Components/Landing/LandingCardGrid.vue';
import LandingLayout from '@/Components/Landing/LandingLayout.vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const title = 'Para Pessoas — Metamorfose Pessoal™ | Talents';
const description =
    'Metamorfose Pessoal™: autoconhecimento, clareza e direcionamento estratégico para diferentes fases da vida pessoal e profissional através do comportamento humano.';

const pillars = [
    { title: 'Mapeamento Comportamental', description: 'Compreensão dos padrões que orientam suas escolhas.', icon: MapIcon },
    { title: 'Análise de Perfil', description: 'Leitura estratégica de fortalezas, desafios e potencialidades.', icon: ClipboardDocumentCheckIcon },
    { title: 'Direcionamento Estratégico', description: 'Clareza prática para decisões pessoais e profissionais.', icon: LightBulbIcon },
];

const benefits = [
    {
        title: 'Autoconhecimento Estratégico',
        description: 'Maior compreensão sobre comportamento, padrões, fortalezas e desafios pessoais.',
        icon: SparklesIcon,
    },
    {
        title: 'Clareza Profissional',
        description: 'Entendimento mais consciente sobre perfil, posicionamento e possibilidades profissionais.',
        icon: LightBulbIcon,
    },
    {
        title: 'Apoio em Escolhas Profissionais',
        description: 'Auxílio para pessoas que estão escolhendo profissão, faculdade ou buscando mais clareza sobre caminhos profissionais.',
        icon: AcademicCapIcon,
    },
    {
        title: 'Transição de Carreira',
        description: 'Direcionamento para momentos de mudança profissional, reposicionamento ou novos ciclos.',
        icon: ArrowPathIcon,
    },
    {
        title: 'Direcionamento em Momentos de Mudança',
        description: 'Apoio estratégico para pessoas que estão vivendo fases de transformação pessoal ou profissional.',
        icon: MapIcon,
    },
    {
        title: 'Melhoria na Comunicação e Relacionamentos',
        description: 'Compreensão da própria forma de se comunicar e se relacionar com diferentes perfis.',
        icon: ChatBubbleLeftRightIcon,
    },
    {
        title: 'Segurança na Tomada de Decisão',
        description: 'Mais consciência sobre comportamento, emoções e impactos das escolhas pessoais e profissionais.',
        icon: ShieldCheckIcon,
    },
    {
        title: 'Desenvolvimento Pessoal e Profissional',
        description: 'Direcionamentos práticos para evolução comportamental, emocional e profissional.',
        icon: UserIcon,
    },
    {
        title: 'Fortalecimento da Confiança e Posicionamento',
        description: 'Maior clareza sobre identidade, potencialidades e desenvolvimento pessoal.',
        icon: SparklesIcon,
    },
    {
        title: 'Inteligência Emocional e Comportamental',
        description: 'Maior percepção emocional, consciência relacional e entendimento dos próprios padrões.',
        icon: HeartIcon,
    },
];

const breadcrumbJsonLd = computed(() =>
    JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { '@type': 'ListItem', position: 1, name: 'Home', item: typeof window !== 'undefined' ? window.location.origin : '' },
            { '@type': 'ListItem', position: 2, name: 'Para Pessoas' },
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
            <p class="text-sm font-semibold uppercase tracking-widest text-talents-600">Para Pessoas</p>
            <p class="mt-4 max-w-3xl text-lg leading-relaxed text-slate-600">
                Autoconhecimento, clareza e direcionamento para diferentes fases da vida pessoal e profissional.
            </p>
            <h1 class="mt-6 text-4xl font-bold leading-tight text-slate-900 md:text-5xl">Metamorfose Pessoal™</h1>
            <p class="mt-6 max-w-3xl text-lg leading-relaxed text-slate-600">
                A Metamorfose Pessoal™ foi criada para gerar mais clareza, consciência e direcionamento através do
                comportamento humano, apoiando pessoas em diferentes fases, decisões e transformações da vida pessoal e
                profissional.
            </p>
        </section>

        <section class="py-12">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-center text-2xl font-bold text-slate-900 md:text-3xl">Pilares da jornada</h2>
                <div class="mt-10">
                    <LandingCardGrid :items="pillars" columns="md:grid-cols-3" />
                </div>
                <div class="mt-10 text-center">
                    <Link :href="route('landing.contato')" class="btn-primary"> Saiba Mais </Link>
                </div>
            </div>
        </section>

        <section class="py-16">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="text-center text-2xl font-bold text-slate-900 md:text-3xl">Benefícios da transformação pessoal</h2>
                <p class="mx-auto mt-3 max-w-2xl text-center text-slate-600">
                    Desenvolvimento humano estratégico — sem linguagem técnica, com clareza e acolhimento.
                </p>
                <div class="mt-10">
                    <LandingCardGrid :items="benefits" columns="md:grid-cols-2 lg:grid-cols-3" />
                </div>
            </div>
        </section>

        <section class="border-t border-white/30 bg-white/30 py-16 backdrop-blur-sm">
            <div class="mx-auto max-w-3xl px-4 text-center">
                <h2 class="text-2xl font-bold text-slate-900 md:text-3xl">Toda transformação começa pelo autoconhecimento.</h2>
                <p class="mx-auto mt-3 max-w-xl text-slate-600">
                    Converse com um especialista Talents e descubra como a Metamorfose Pessoal™ pode apoiar seu
                    momento.
                </p>
                <Link :href="route('landing.contato')" class="btn-primary mt-8"> Falar com Especialista </Link>
            </div>
        </section>
    </LandingLayout>
</template>
