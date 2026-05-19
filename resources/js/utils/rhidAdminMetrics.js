import { formatRhidBankBalanceMinutes } from '@/utils/rhidDate';

export function operationalAlertLabel(level) {
    switch (level) {
        case 'high':
            return 'Crítico';
        case 'medium':
            return 'Atenção';
        default:
            return 'OK';
    }
}

export function operationalAlertClass(level) {
    switch (level) {
        case 'high':
            return 'bg-rose-100 text-rose-800 ring-rose-200';
        case 'medium':
            return 'bg-amber-100 text-amber-900 ring-amber-200';
        default:
            return 'bg-emerald-100 text-emerald-800 ring-emerald-200';
    }
}

export function formatPortfolioBankAvg(minutes) {
    if (minutes == null || !Number.isFinite(Number(minutes))) {
        return '—';
    }
    return formatRhidBankBalanceMinutes(minutes);
}

/**
 * Mapeia payload da API admin para props do RhidOverviewKpiCards.
 * @param {Record<string, unknown>|null} m
 */
export function metricsToKpiProps(m) {
    if (!m || m.status !== 'ok') {
        return null;
    }
    const bank = m.bank || {};
    const adherence = m.adherence || {};
    const just = m.justifications || {};
    const punches = m.punches || {};

    return {
        overviewPunchRowsLength: punches.count ?? 0,
        overviewPunchDistinct: punches.distinct_collaborators ?? 0,
        overviewBankNumericRowsLength: bank.numeric_count ?? 0,
        overviewBankNegativeCount: bank.negative_count ?? 0,
        overviewBankAvgMinutes: bank.avg_minutes ?? null,
        overviewBankAvgMomDeltaMinutes: bank.mom_delta_minutes ?? null,
        overviewAdherenceDiasCalendario: adherence.dias ?? null,
        overviewAdherenceColabs: adherence.colabs ?? 0,
        overviewAdherenceDiasMomDelta: adherence.dias_mom_delta ?? null,
        overviewAdherenceColabsMomDelta: adherence.colabs_mom_delta ?? null,
        overviewJustTotal: just.total ?? null,
        overviewJustAtestados: just.atestados ?? null,
        overviewJustTotalMomDelta: just.total_mom_delta ?? null,
        overviewJustAtestadosMomDelta: just.atestados_mom_delta ?? null,
    };
}
