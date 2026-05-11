<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useAdminPermissions } from '@/composables/useAdminPermissions';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    users: Array,
});

const { canAdmin } = useAdminPermissions();
const page = usePage();

const remove = (user) => {
    if (user.is_owner || user.id === page.props.auth?.user?.id) {
        return;
    }
    if (confirm('Remover este utilizador?')) {
        router.delete(route('admin.users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Equipe — Administradores" />

    <AdminLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-900">Equipe Talents</h2>
                    <p class="text-sm text-gray-600">Super administradores da plataforma</p>
                </div>
                <Link v-if="canAdmin('equipe', 'create')" :href="route('admin.users.create')">
                    <PrimaryButton>Novo administrador</PrimaryButton>
                </Link>
            </div>
        </template>

        <div class="surface-card overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700">Nome</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700">E-mail</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700">Função</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700">Comercial</th>
                        <th class="px-4 py-2 text-left font-semibold text-slate-700">Estado</th>
                        <th class="px-4 py-2 text-right font-semibold text-slate-700">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                    <tr v-for="u in users" :key="u.id">
                        <td class="px-4 py-2 font-medium text-slate-900">{{ u.name }}</td>
                        <td class="px-4 py-2 text-slate-700">{{ u.email }}</td>
                        <td class="px-4 py-2 text-slate-700">
                            <span
                                v-if="u.is_owner"
                                class="inline-flex rounded-full bg-violet-100 px-2 py-0.5 text-xs font-medium text-violet-800"
                            >
                                Proprietário
                            </span>
                            <span v-else class="text-slate-600">Administrador</span>
                        </td>
                        <td class="px-4 py-2 text-slate-700">
                            {{ u.is_commercial ? 'Sim' : 'Não' }}
                        </td>
                        <td class="px-4 py-2">
                            <span :class="u.is_active ? 'text-emerald-700' : 'text-red-600'">
                                {{ u.is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </td>
                        <td class="space-x-3 px-4 py-2 text-right">
                            <Link
                                v-if="canAdmin('equipe', 'edit')"
                                :href="route('admin.users.edit', u.id)"
                                class="font-medium text-talents-700 hover:underline"
                            >
                                Editar
                            </Link>
                            <button
                                v-if="canAdmin('equipe', 'delete') && !u.is_owner && u.id !== page.props.auth?.user?.id"
                                type="button"
                                class="font-medium text-red-600 hover:underline"
                                @click="remove(u)"
                            >
                                Remover
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
