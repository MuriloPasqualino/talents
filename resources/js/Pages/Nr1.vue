<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});

const showContactModal = ref(false);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    company: '',
    message: '',
});

const submitInterest = () => {
    form.post(route('landing.interest'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('name', 'email', 'phone', 'company', 'message');
            showContactModal.value = false;
        },
    });
};

const onKeydown = (e) => {
    if (e.key === 'Escape') {
        showContactModal.value = false;
    }
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onUnmounted(() => window.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Head title="Talents — NR-1 e riscos psicossociais" />

    <div class="app-shell min-h-screen scroll-smooth text-slate-900">
        <header class="sticky top-0 z-20 border-b border-white/40 bg-white/70 shadow-sm backdrop-blur-md">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 py-4">
                <Link href="/" class="inline-flex items-center gap-3">
                    <img src="/images/logo.png" alt="Talents" class="h-10 w-auto" />
                </Link>
                <nav class="flex flex-wrap items-center justify-end gap-2 sm:gap-4">
                    <Link href="/" class="text-sm font-semibold text-talents-700 hover:underline"> Início </Link>
                    <Link
                        v-if="canRegister && !$page.props.auth.user"
                        :href="route('register')"
                        class="text-sm font-semibold text-talents-700 hover:underline"
                    >
                        Criar conta
                    </Link>
                    <Link
                        v-if="canLogin && !$page.props.auth.user"
                        :href="route('login')"
                        class="rounded-full bg-talents-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-talents-700"
                    >
                        Entrar
                    </Link>
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="text-sm font-semibold text-talents-700 hover:underline"
                    >
                        Painel
                    </Link>
                </nav>
            </div>
        </header>

        <main>
            <section class="mx-auto max-w-6xl px-4 py-16 md:py-20">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-widest text-talents-600">
                        NR-1 · Para quem leva pessoas a sério
                    </p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight text-slate-900 md:text-5xl">
                        A Talents ajuda empresas a caminhar rumo à
                        <span class="text-talents-700">conformidade com a NR-1</span> na gestão de riscos psicossociais
                    </h1>
                    <p class="mt-6 text-lg text-slate-600">
                        Com <strong>método</strong>, <strong>ciência</strong> e <strong>estratégia</strong> — da identificação ao
                        monitoramento, com rastreabilidade para o PGR.
                    </p>

                    <div class="mt-6 flex flex-wrap items-center gap-2 md:gap-3">
                        <span
                            class="inline-flex items-center rounded-full border-2 border-talents-600 bg-talents-50 px-4 py-2 text-sm font-bold text-talents-800 shadow-sm"
                            >IDENTIFICAR</span
                        >
                        <span class="text-talents-400">→</span>
                        <span
                            class="inline-flex items-center rounded-full border-2 border-talents-600 bg-talents-50 px-4 py-2 text-sm font-bold text-talents-800 shadow-sm"
                            >📊 AVALIAR</span
                        >
                        <span class="text-talents-400">→</span>
                        <span
                            class="inline-flex items-center rounded-full border-2 border-talents-600 bg-talents-50 px-4 py-2 text-sm font-bold text-talents-800 shadow-sm"
                            >⚙️ IMPLEMENTAR</span
                        >
                        <span class="text-talents-400">→</span>
                        <span
                            class="inline-flex items-center rounded-full border-2 border-talents-600 bg-talents-50 px-4 py-2 text-sm font-bold text-talents-800 shadow-sm"
                            >📈 MONITORAR</span
                        >
                    </div>

                    <blockquote class="mt-6 border-l-4 border-talents-600 pl-4 text-lg italic text-slate-700">
                        Gestão de risco psicossocial não é evento.
                        <span class="font-semibold not-italic">É processo.</span>
                    </blockquote>

                    <div class="mt-8 flex flex-wrap gap-4">
                        <button
                            type="button"
                            class="rounded-full bg-talents-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:bg-talents-700"
                            @click="showContactModal = true"
                        >
                            Ver na prática
                        </button>
                    </div>
                    <p class="mt-6 text-sm text-slate-500">
                        Pesquisas com anonimato protegido · Resultados prontos para decisão · Adequação às exigências de saúde e
                        segurança do trabalho
                    </p>
                </div>
            </section>

            <section class="border-y border-white/30 bg-white/40 py-16 backdrop-blur-sm">
                <div class="mx-auto max-w-[1600px] px-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 xl:gap-5">
                        <article class="surface-card flex h-full flex-col border-t-4 border-talents-600 p-5 shadow-md sm:p-6">
                            <h3 class="text-base font-bold leading-snug text-slate-900 md:text-lg">
                                1️⃣ Buscar orientação técnica especializada
                            </h3>
                            <p class="mt-3 flex-1 text-sm leading-relaxed text-slate-600">
                                A NR-1 exige método e estrutura. Ter apoio especializado evita improviso e exposição jurídica.
                            </p>
                        </article>

                        <article class="surface-card flex h-full flex-col border-t-4 border-red-500 p-5 shadow-md sm:p-6">
                            <h3 class="text-base font-bold leading-snug text-slate-900 md:text-lg">2️⃣ IDENTIFICAR</h3>
                            <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-slate-600">
                                <li>Sobrecarga e pressão excessiva</li>
                                <li>Conflitos recorrentes</li>
                                <li>Liderança despreparada</li>
                                <li>Lacunas no PGR</li>
                            </ul>
                        </article>

                        <article class="surface-card flex h-full flex-col border-t-4 border-amber-500 p-5 shadow-md sm:p-6">
                            <h3 class="text-base font-bold leading-snug text-slate-900 md:text-lg">3️⃣ AVALIAR</h3>
                            <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-slate-600">
                                <li>Classificar nível de exposição (baixo, médio, alto)</li>
                                <li>Definir prioridades</li>
                                <li>Avaliar impacto organizacional</li>
                            </ul>
                        </article>

                        <article class="surface-card flex h-full flex-col border-t-4 border-emerald-600 p-5 shadow-md sm:p-6">
                            <h3 class="text-base font-bold leading-snug text-slate-900 md:text-lg">4️⃣ IMPLEMENTAR</h3>
                            <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-slate-600">
                                <li>Estruturar plano de ação</li>
                                <li>Treinamentos e ajustes organizacionais</li>
                                <li>Formalização no PGR</li>
                            </ul>
                        </article>

                        <article class="surface-card flex h-full flex-col border-t-4 border-blue-600 p-5 shadow-md sm:p-6">
                            <h3 class="text-base font-bold leading-snug text-slate-900 md:text-lg">5️⃣ MONITORAR</h3>
                            <ul class="mt-3 list-inside list-disc space-y-1.5 text-sm text-slate-600">
                                <li>Acompanhar indicadores humanos</li>
                                <li>Reavaliar periodicamente</li>
                                <li>Atualizar plano de ação</li>
                            </ul>
                        </article>
                    </div>
                </div>
            </section>

            <section id="contato" class="border-t border-white/30 bg-white/30 py-16 backdrop-blur-sm">
                <div class="mx-auto max-w-2xl px-4">
                    <h2 class="text-center text-2xl font-bold text-slate-900 md:text-3xl">Quer conhecer mais a Talents?</h2>
                    <p class="mx-auto mt-3 max-w-xl text-center text-slate-600">
                        Deixe seus dados (telefone/WhatsApp ajuda no retorno) e, se quiser, uma mensagem.
                    </p>

                    <div
                        v-if="$page.props.flash?.success"
                        class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm text-emerald-800"
                    >
                        {{ $page.props.flash.success }}
                    </div>
                    <div
                        v-if="$page.props.flash?.error"
                        class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-center text-sm text-red-800"
                    >
                        {{ $page.props.flash.error }}
                    </div>

                    <form class="surface-card mt-8 space-y-5 p-6 shadow-md sm:p-8" @submit.prevent="submitInterest">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-interest-name">Nome</label>
                            <input
                                id="nr1-interest-name"
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="name"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-interest-email">E-mail</label>
                            <input
                                id="nr1-interest-email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-interest-phone"
                                >Telefone / WhatsApp <span class="font-normal text-slate-500">(opcional)</span></label
                            >
                            <input
                                id="nr1-interest-phone"
                                v-model="form.phone"
                                type="tel"
                                autocomplete="tel"
                                placeholder="DDD + número"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-interest-company"
                                >Empresa <span class="font-normal text-slate-500">(opcional)</span></label
                            >
                            <input
                                id="nr1-interest-company"
                                v-model="form.company"
                                type="text"
                                autocomplete="organization"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.company" class="mt-1 text-sm text-red-600">{{ form.errors.company }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-interest-message"
                                >Mensagem <span class="font-normal text-slate-500">(opcional)</span></label
                            >
                            <textarea
                                id="nr1-interest-message"
                                v-model="form.message"
                                rows="4"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                                placeholder="Conte um pouco do que você busca ou deixe em branco."
                            />
                            <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">{{ form.errors.message }}</p>
                        </div>
                        <div class="pt-2">
                            <button
                                type="submit"
                                class="w-full rounded-full bg-talents-600 px-6 py-3 text-sm font-bold text-white shadow-md transition hover:bg-talents-700 disabled:opacity-60 sm:w-auto"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Enviando…' : 'Enviar interesse' }}
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </main>

        <footer class="border-t border-white/30 py-8 text-center text-xs text-slate-500 backdrop-blur-sm">
            <Link href="/" class="text-talents-700 hover:underline">Voltar à página inicial</Link>
            <span class="mx-2 text-slate-300">·</span>
            Talents &mdash; Gestão de Pessoas
        </footer>

        <Teleport to="body">
            <div
                v-if="showContactModal"
                class="fixed inset-0 z-[100] flex items-end justify-center bg-black/60 p-4 sm:items-center"
                role="dialog"
                aria-modal="true"
                @click.self="showContactModal = false"
            >
                <div
                    class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl"
                    @click.stop
                >
                    <div class="sticky top-0 flex items-center justify-between border-b border-slate-200 bg-white px-5 py-4">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Ver na prática</h3>
                            <p class="text-sm text-slate-600">Fale com um especialista Talents</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-800"
                            aria-label="Fechar"
                            @click="showContactModal = false"
                        >
                            ✕
                        </button>
                    </div>
                    <form class="space-y-4 px-5 py-5" @submit.prevent="submitInterest">
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-modal-name">Nome</label>
                            <input
                                id="nr1-modal-name"
                                v-model="form.name"
                                type="text"
                                required
                                autocomplete="name"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-modal-email">E-mail</label>
                            <input
                                id="nr1-modal-email"
                                v-model="form.email"
                                type="email"
                                required
                                autocomplete="email"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-modal-phone"
                                >Telefone / WhatsApp <span class="font-normal text-slate-500">(opcional)</span></label
                            >
                            <input
                                id="nr1-modal-phone"
                                v-model="form.phone"
                                type="tel"
                                autocomplete="tel"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-modal-company">Empresa (opcional)</label>
                            <input
                                id="nr1-modal-company"
                                v-model="form.company"
                                type="text"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700" for="nr1-modal-message">Mensagem (opcional)</label>
                            <textarea
                                id="nr1-modal-message"
                                v-model="form.message"
                                rows="3"
                                class="mt-1 w-full rounded-lg border-slate-200 shadow-sm focus:border-talents-500 focus:ring-talents-500"
                            />
                        </div>
                        <button
                            type="submit"
                            class="w-full rounded-full bg-talents-600 py-3 text-sm font-bold text-white shadow hover:bg-talents-700 disabled:opacity-60"
                            :disabled="form.processing"
                        >
                            {{ form.processing ? 'Enviando…' : 'Enviar' }}
                        </button>
                    </form>
                </div>
            </div>
        </Teleport>
    </div>
</template>
