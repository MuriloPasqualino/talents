<?php

namespace App\Services\Solides;

/**
 * Agrupa candidatos em "vagas inferidas" sem endpoint nativo de vagas na API Gestão.
 */
final class SolidesInferredVacancyGrouper
{
    public const UNIDENTIFIED_LABEL = 'Sem vaga identificada';

    /**
     * @param  array<string, mixed>  $row  item de GET /curriculos
     * @return array<string, mixed>
     */
    public static function normalizeCurriculoRow(array $row): array
    {
        $origin = self::scrubStr($row['origin'] ?? null);
        $seniority = self::scrubStr($row['seniority'] ?? null);

        return [
            'source' => 'curriculo',
            'solides_id' => isset($row['id']) ? (int) $row['id'] : null,
            'name' => self::scrubStr($row['fullName'] ?? null, ''),
            'email' => self::scrubStr($row['mainEmail'] ?? null),
            'cpf' => self::scrubStr($row['idNumber'] ?? null),
            'origin' => $origin,
            'seniority_or_profile' => $seniority,
            'inferred_key' => self::inferKey($origin, $seniority),
        ];
    }

    /**
     * @param  array<string, mixed>  $row  item de GET /candidatos (passaporte)
     * @return array<string, mixed>
     */
    public static function normalizePassaporteRow(array $row): array
    {
        $profile = self::scrubStr($row['profile'] ?? null);

        return [
            'source' => 'passaporte',
            'solides_id' => isset($row['id']) ? (int) $row['id'] : null,
            'name' => self::scrubStr($row['name'] ?? null, ''),
            'email' => self::scrubStr($row['email'] ?? null),
            'cpf' => self::scrubStr($row['idNumber'] ?? null),
            'origin' => null,
            'seniority_or_profile' => $profile !== '' ? $profile : null,
            'inferred_key' => $profile !== '' ? $profile : self::UNIDENTIFIED_LABEL,
        ];
    }

    public static function inferKey(?string $origin, ?string $seniorityOrProfile): string
    {
        $o = $origin !== null ? trim($origin) : '';
        if ($o !== '') {
            return $o;
        }
        $s = $seniorityOrProfile !== null ? trim($seniorityOrProfile) : '';
        if ($s !== '') {
            return $s;
        }

        return self::UNIDENTIFIED_LABEL;
    }

    /**
     * Chave estável para deduplicação (e-mail ou CPF só dígitos).
     */
    public static function dedupePersonKey(?string $email, ?string $cpf, ?int $solidesId, string $source): string
    {
        $e = $email !== null ? strtolower(trim($email)) : '';
        if ($e !== '') {
            return 'e:'.$e;
        }
        $digits = $cpf !== null ? preg_replace('/\D+/', '', $cpf) : '';
        if ($digits !== '') {
            return 'c:'.$digits;
        }
        if ($solidesId !== null) {
            return $source.':id:'.$solidesId;
        }

        return $source.':anon:'.bin2hex(random_bytes(8));
    }

    /**
     * @param  list<array<string, mixed>>  $candidates  linhas já normalizadas
     * @return array{summary: array{total: int, with_vacancy_label: int, unidentified: int}, groups: list<array{inferred_key: string, count: int, candidates: list<array<string, mixed>>}>}
     */
    public static function buildGroupedCatalog(
        array $candidates,
        ?string $originFilter,
        ?string $groupKeyContains,
    ): array {
        $needleOrigin = $originFilter !== null && trim($originFilter) !== '' ? mb_strtolower(trim($originFilter), 'UTF-8') : null;
        $needleGroup = $groupKeyContains !== null && trim($groupKeyContains) !== '' ? mb_strtolower(trim($groupKeyContains), 'UTF-8') : null;

        $filtered = array_values(array_filter($candidates, function (array $c) use ($needleOrigin): bool {
            if ($needleOrigin === null) {
                return true;
            }
            $origin = isset($c['origin']) && is_string($c['origin']) ? mb_strtolower($c['origin'], 'UTF-8') : '';
            $prof = isset($c['seniority_or_profile']) && is_string($c['seniority_or_profile'])
                ? mb_strtolower($c['seniority_or_profile'], 'UTF-8') : '';

            return str_contains($origin, $needleOrigin) || str_contains($prof, $needleOrigin);
        }));

        $groupsMap = [];
        foreach ($filtered as $c) {
            $key = isset($c['inferred_key']) && is_string($c['inferred_key']) ? $c['inferred_key'] : self::UNIDENTIFIED_LABEL;
            if (! isset($groupsMap[$key])) {
                $groupsMap[$key] = [];
            }
            $groupsMap[$key][] = $c;
        }

        if ($needleGroup !== null) {
            foreach (array_keys($groupsMap) as $key) {
                if (! str_contains(mb_strtolower((string) $key, 'UTF-8'), $needleGroup)) {
                    unset($groupsMap[$key]);
                }
            }
        }

        $flat = [];
        foreach ($groupsMap as $members) {
            foreach ($members as $m) {
                $flat[] = $m;
            }
        }

        $withVacancy = 0;
        $unidentified = 0;
        foreach ($flat as $c) {
            $k = $c['inferred_key'] ?? self::UNIDENTIFIED_LABEL;
            if ($k === self::UNIDENTIFIED_LABEL) {
                $unidentified++;
            } else {
                $withVacancy++;
            }
        }

        $groups = [];
        foreach ($groupsMap as $inferredKey => $members) {
            $groups[] = [
                'inferred_key' => $inferredKey,
                'count' => count($members),
                'candidates' => array_values($members),
            ];
        }

        usort($groups, function (array $a, array $b): int {
            if ($a['count'] !== $b['count']) {
                return $b['count'] <=> $a['count'];
            }

            return strcmp((string) $a['inferred_key'], (string) $b['inferred_key']);
        });

        return [
            'summary' => [
                'total' => count($flat),
                'with_vacancy_label' => $withVacancy,
                'unidentified' => $unidentified,
            ],
            'groups' => $groups,
        ];
    }

    /**
     * Mescla currículos + passaportes; currículo prevalece na deduplicação por pessoa.
     *
     * @param  list<array<string, mixed>>  $curriculosRaw
     * @param  list<array<string, mixed>>  $passaportesRaw
     * @return list<array<string, mixed>>
     */
    public static function mergeAndDedupe(array $curriculosRaw, array $passaportesRaw): array
    {
        $byDedupe = [];

        foreach ($curriculosRaw as $raw) {
            if (! is_array($raw)) {
                continue;
            }
            $n = self::normalizeCurriculoRow($raw);
            $dk = self::dedupePersonKey($n['email'] ?? null, $n['cpf'] ?? null, $n['solides_id'] ?? null, 'curriculo');
            $byDedupe[$dk] = $n;
        }

        foreach ($passaportesRaw as $raw) {
            if (! is_array($raw)) {
                continue;
            }
            $n = self::normalizePassaporteRow($raw);
            $dk = self::dedupePersonKey($n['email'] ?? null, $n['cpf'] ?? null, $n['solides_id'] ?? null, 'passaporte');
            if (! isset($byDedupe[$dk])) {
                $byDedupe[$dk] = $n;
            }
        }

        return array_values($byDedupe);
    }

    private static function scrubStr(mixed $value, ?string $whenEmpty = null): ?string
    {
        if ($value === null) {
            return $whenEmpty;
        }
        if (! is_string($value)) {
            $value = (string) $value;
        }
        $value = trim($value);
        if ($value === '') {
            return $whenEmpty;
        }

        $clean = mb_scrub($value, 'UTF-8');

        return $clean === '' ? $whenEmpty : $clean;
    }
}
