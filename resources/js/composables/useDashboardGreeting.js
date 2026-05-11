import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Saudação por horário + primeiro nome para dashboards.
 */
export function useDashboardGreeting() {
    const page = usePage();

    return computed(() => {
        const hour = new Date().getHours();
        let prefix = 'Olá';
        if (hour < 12) prefix = 'Bom dia';
        else if (hour < 18) prefix = 'Boa tarde';
        else prefix = 'Boa noite';

        const full = page.props.auth?.user?.name?.trim() || '';
        const first = full.split(/\s+/).filter(Boolean)[0] || full;

        return { prefix, first, full };
    });
}
