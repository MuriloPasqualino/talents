/**
 * Utilitários para datas "só-dia" (sem hora, ex.: cast `date` no Laravel).
 *
 * Laravel serializa colunas `date` como `YYYY-MM-DDT00:00:00.000000Z` (UTC).
 * Em fusos negativos (BRT = UTC-3) isto "anda" para o dia anterior quando
 * convertido para hora local. Aqui ignoramos a hora UTC e construímos um
 * Date local ao meio-dia para evitar deslocamentos por timezone/DST.
 */

/**
 * Devolve um `Date` local ao meio-dia para a parte `YYYY-MM-DD` de uma string.
 *
 * @param {string|null|undefined} iso
 * @returns {Date|null}
 */
export function parseDateOnly(iso) {
    if (!iso) return null;
    const s = String(iso).trim();
    if (!s) return null;
    const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (m) {
        const [, y, mo, dd] = m;
        return new Date(Number(y), Number(mo) - 1, Number(dd), 12, 0, 0);
    }
    const d = new Date(s);
    return Number.isNaN(d.getTime()) ? null : d;
}

function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Ex.: "Terça-feira, 13 de maio de 2026"
 */
export function formatDateLong(iso) {
    const d = parseDateOnly(iso);
    if (!d) return '';
    return capitalizeFirst(
        d.toLocaleDateString('pt-BR', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        }),
    );
}

/**
 * Ex.: "13 mai" (sem ano)
 */
export function formatDateShort(iso) {
    const d = parseDateOnly(iso);
    if (!d) return '—';
    return d.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
}

/**
 * Início do dia local (00:00) para comparar com outras datas locais.
 *
 * @param {Date} d
 */
export function startOfLocalDay(d) {
    return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

/**
 * Diferença em dias entre a data informada e hoje (0 = hoje, 1 = amanhã, -1 = ontem).
 *
 * @param {string|null|undefined} iso
 * @returns {number|null}
 */
export function daysFromToday(iso) {
    const d = parseDateOnly(iso);
    if (!d) return null;
    const e0 = startOfLocalDay(d).getTime();
    const t0 = startOfLocalDay(new Date()).getTime();
    return Math.round((e0 - t0) / 86400000);
}
