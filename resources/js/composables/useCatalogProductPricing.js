/**
 * Espelha CommercialProductPricingService (PHP) para cálculo ao vivo na proposta.
 */

const pickTier = (n, maxes, values) => {
    for (let i = 0; i < maxes.length; i++) {
        if (n <= Number(maxes[i] ?? 0)) {
            return Number(values[i] ?? 0);
        }
    }
    return Number(values[maxes.length] ?? 0);
};

export const FLEXIBLE_RATE_DEFS = [
    { key: 'hour', label: 'Por hora', unitsLabel: 'Quantidade de horas', suffix: 'h' },
    { key: 'quantity', label: 'Por quantidade', unitsLabel: 'Quantidade', suffix: 'un.' },
    { key: 'unit', label: 'Por unidade', unitsLabel: 'Número de unidades', suffix: 'un.' },
];

const flexibleRatesTotal = (config, selection) => {
    const mode = String(selection.rate_mode ?? '');
    const rate = config.rates?.[mode];
    if (!rate?.enabled) {
        return { subtotal: 0, total: 0 };
    }

    const units = Math.max(0, Number(selection.units ?? 0));
    if (units <= 0) {
        return { subtotal: 0, total: 0 };
    }

    const subtotal = Math.round(units * Number(rate.cents_per_unit ?? 0));
    const adjustment = String(selection.adjustment ?? 'none');

    let total = subtotal;
    if (adjustment === 'bonus') {
        total = 0;
    } else if (adjustment === 'discount') {
        const pct = Math.min(100, Math.max(0, Number(selection.discount_percent ?? 0)));
        total = Math.round(subtotal * (1 - pct / 100));
    }

    return { subtotal, total: Math.max(0, total) };
};

const buildFlexibleDetail = (selection, flexible, config) => {
    if (flexible.subtotal <= 0) {
        return '—';
    }

    const mode = String(selection.rate_mode ?? '');
    const def = FLEXIBLE_RATE_DEFS.find((d) => d.key === mode);
    const units = Number(selection.units ?? 0);
    const centsPerUnit = Number(config.rates?.[mode]?.cents_per_unit ?? 0);
    const suffix = def?.suffix ?? '';

    const unitsFmt = units.toLocaleString('pt-BR', { maximumFractionDigits: 2 });
    const priceFmt = (centsPerUnit / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const base = `${unitsFmt} ${suffix} × ${priceFmt}`;

    const adjustment = String(selection.adjustment ?? 'none');
    if (adjustment === 'bonus') {
        return `${base} · Bonificação`;
    }
    if (adjustment === 'discount') {
        const pct = Number(selection.discount_percent ?? 0).toLocaleString('pt-BR', { maximumFractionDigits: 2 });
        return `${base} · Desconto ${pct}%`;
    }
    return base;
};

const shouldIncludeLine = (selection, result) => {
    if (!selection?.enabled) {
        return false;
    }
    if (result.total_cents > 0) {
        return true;
    }
    return selection.adjustment === 'bonus' && (result.subtotal_cents ?? 0) > 0;
};

/**
 * @param {object} product
 * @param {number} employees
 * @param {object} selection
 */
export function calculateCatalogLine(product, employees, selection) {
    if (!selection?.enabled) {
        return { total_cents: 0, subtotal_cents: 0, detail: '' };
    }

    const config = product.pricing_config || {};
    const n = Math.max(0, Number(employees ?? 0));
    let total = 0;
    let subtotal = 0;
    let detail = '—';

    switch (product.pricing_type) {
        case 'fixed':
            total = Number(config.amount_cents ?? 0);
            subtotal = total;
            break;
        case 'per_employee':
            total = n > 0 ? n * Number(config.cents_per_employee ?? 0) : 0;
            subtotal = total;
            break;
        case 'tiered_per_employee':
            if (n > 0) {
                total = n * pickTier(
                    n,
                    [config.tier1_max, config.tier2_max, config.tier3_max],
                    [config.tier1_cents, config.tier2_cents, config.tier3_cents, config.tier4_cents],
                );
            }
            subtotal = total;
            break;
        case 'fixed_modality': {
            const mod = String(selection.modality ?? '');
            const found = (config.modalities || []).find((m) => m.key === mod);
            total = found ? Number(found.cents ?? 0) : 0;
            subtotal = total;
            break;
        }
        case 'salary_times_employees':
            total = n > 0 ? n * Math.max(0, Number(selection.salary_cents ?? 0)) : 0;
            subtotal = total;
            break;
        case 'threshold_multiplier': {
            const base = Number(config.base_cents ?? 0);
            const threshold = Number(config.threshold_employees ?? 30);
            const multiplier = Math.max(1, Number(config.multiplier ?? 2));
            total = n > threshold ? base * multiplier : base;
            subtotal = total;
            break;
        }
        case 'flexible_rates': {
            const flex = flexibleRatesTotal(config, selection);
            total = flex.total;
            subtotal = flex.subtotal;
            detail = buildFlexibleDetail(selection, flex, config);
            break;
        }
        default:
            total = 0;
            subtotal = 0;
    }

    total = Math.max(0, total);
    subtotal = Math.max(0, subtotal);

    if (detail === '—' && total > 0) {
        switch (product.pricing_type) {
            case 'fixed':
                detail = 'Valor fixo';
                break;
            case 'per_employee':
            case 'tiered_per_employee':
                detail = `${n} funcionários`;
                break;
            case 'fixed_modality': {
                const mod = (config.modalities || []).find((m) => m.key === selection.modality);
                detail = mod?.label || selection.modality || '—';
                break;
            }
            case 'salary_times_employees':
                detail = `Salário × ${n} funcionários`;
                break;
            case 'threshold_multiplier':
                detail = n > Number(config.threshold_employees ?? 30) ? 'Pacote ampliado' : 'Pacote padrão';
                break;
            default:
                break;
        }
    }

    return { total_cents: total, subtotal_cents: subtotal, detail };
}

/**
 * @param {Array<object>} products
 * @param {number} employees
 * @param {Array<object>} selections
 */
export function calculateCatalogProducts(products, employees, selections) {
    const byId = Object.fromEntries((products || []).map((p) => [p.id, p]));
    const lines = [];
    let total = 0;

    (selections || []).forEach((selection) => {
        const product = byId[selection.product_id];
        if (!product) return;
        const result = calculateCatalogLine(product, employees, selection);
        if (!shouldIncludeLine(selection, result)) return;
        lines.push({
            product_id: product.id,
            key: product.slug,
            label: product.name,
            detail: result.detail,
            value_cents: result.total_cents,
        });
        total += result.total_cents;
    });

    return { total_cents: total, lines };
}

export function enabledFlexibleRates(product) {
    const rates = product?.pricing_config?.rates || {};
    return FLEXIBLE_RATE_DEFS.filter((def) => rates[def.key]?.enabled);
}
