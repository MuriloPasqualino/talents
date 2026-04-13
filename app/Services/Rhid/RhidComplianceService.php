<?php

namespace App\Services\Rhid;

use App\Exceptions\RhidApiException;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Client\Response;

class RhidComplianceService
{
    public function __construct(
        private RhidClient $client,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function listJustificationTypes(Company $company, ?User $user, array $query = []): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'GET',
            'customerdb/justificationtype.svc/a',
            ['query' => $query, 'auditAction' => 'rhid.justification_types.list'],
        );

        return $this->decodeJson($r, 'justification_types');
    }

    /**
     * @return array<string, mixed>
     */
    public function listAlertTypes(Company $company, ?User $user, array $query = []): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'GET',
            'customerdb/alerttype.svc/a',
            ['query' => $query, 'auditAction' => 'rhid.alert_types.list'],
        );

        return $this->decodeJson($r, 'alert_types');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function listJustifications(Company $company, ?User $user, array $payload): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'POST',
            'customerdb/justification.svc/list',
            [
                'body' => $payload,
                'auditAction' => 'rhid.justifications.list',
            ],
        );

        return $this->decodeJson($r, 'justifications.list');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function createJustification(Company $company, ?User $user, array $payload): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'POST',
            'customerdb/justification.svc/a',
            [
                'body' => $payload,
                'auditAction' => 'rhid.justifications.create',
            ],
        );

        return $this->decodeJson($r, 'justifications.create');
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function massJustification(Company $company, ?User $user, array $payload): array
    {
        if (isset($payload['inicio']) && ! isset($payload['ínicio'])) {
            $payload['ínicio'] = $payload['inicio'];
            unset($payload['inicio']);
        }

        $r = $this->client->request(
            $company,
            $user,
            'POST',
            'customerdb/justification.svc/justificativa_em_massa',
            [
                'body' => $payload,
                'auditAction' => 'rhid.justifications.mass',
            ],
        );

        return $this->decodeJson($r, 'justifications.mass');
    }

    /**
     * @return array<string, mixed>
     */
    public function deleteJustification(Company $company, ?User $user, int $id): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'DELETE',
            'customerdb/justification.svc/a/'.$id,
            ['auditAction' => 'rhid.justifications.delete'],
        );

        return $this->decodeJson($r, 'justifications.delete');
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    public function personBankHours(Company $company, ?User $user, array $query): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'GET',
            'customerdb/person.svc/person_banco_horas',
            [
                'query' => $query,
                'auditAction' => 'rhid.person_banco_horas',
            ],
        );

        $json = $r->json();
        if (! is_array($json)) {
            throw RhidApiException::fromResponse($r, 'person_banco_horas');
        }

        $rows = $this->normalizeBankHoursRows($json);
        $out = [];
        foreach ($rows as $row) {
            if (is_array($row)) {
                $out[] = $this->mergePersonNestedIntoBankHourRow($row);
            }
        }

        return $this->shrinkBankHourRowsForClient($out);
    }

    /**
     * Lista colaboradores (cadastro RHID).
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function listPersons(Company $company, ?User $user, array $query = []): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'GET',
            'customerdb/person.svc/a',
            [
                'query' => $query,
                'auditAction' => 'rhid.person.list',
            ],
        );

        return $this->decodeJson($r, 'person.list');
    }

    /**
     * Banco de horas na data de referencia.
     *
     * Por padrao: uma unica chamada a person_banco_horas?date= (API RHID).
     * Com config rhid.bank_hours_aggregate: lista colaboradores paginada + consultas em lotes.
     *
     * @return array{date: string, rows: list<array<string, mixed>>, source: string}
     */
    public function allPersonBankHoursAggregated(
        Company $company,
        ?User $user,
        string $date,
        int $listPageSize = 200,
        int $bankChunk = 50,
    ): array {
        if (! config('rhid.bank_hours_aggregate')) {
            $rows = $this->personBankHours($company, $user, ['date' => $date]);

            return [
                'date' => $date,
                'rows' => $rows,
                'source' => 'person_banco_horas',
            ];
        }

        $listPageSize = max(1, min(500, $listPageSize));
        $bankChunk = max(1, min(200, $bankChunk));

        $ids = [];
        try {
            $page = 0;
            while (true) {
                $listJson = $this->listPersons($company, $user, [
                    'page' => $page,
                    'maxSize' => $listPageSize,
                ]);
                $batch = $this->extractPersonIdsFromListResponse($listJson);
                foreach ($batch as $id) {
                    $ids[] = $id;
                }
                if (count($batch) < $listPageSize) {
                    break;
                }
                $page++;
                if ($page > 500) {
                    break;
                }
            }
        } catch (RhidApiException) {
            $rows = $this->personBankHours($company, $user, ['date' => $date]);

            return [
                'date' => $date,
                'rows' => $rows,
                'source' => 'person_banco_horas_sem_lista',
            ];
        }

        $ids = array_values(array_unique(array_filter($ids)));

        if ($ids === []) {
            $rows = $this->personBankHours($company, $user, ['date' => $date]);

            return [
                'date' => $date,
                'rows' => $rows,
                'source' => 'person_banco_horas_sem_ids',
            ];
        }

        $merged = [];
        foreach (array_chunk($ids, $bankChunk) as $chunk) {
            $part = $this->personBankHours($company, $user, [
                'date' => $date,
                'people' => array_values($chunk),
            ]);
            foreach ($part as $row) {
                $merged[] = $row;
            }
        }

        return [
            'date' => $date,
            'rows' => $merged,
            'source' => 'aggregated_by_person_ids',
        ];
    }

    /**
     * Remove campos sensiveis e pesados da resposta RHID antes de enviar ao browser.
     *
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    protected function shrinkBankHourRowsForClient(array $rows): array
    {
        $keep = array_flip([
            'name', 'nome', 'strNome', 'strName', 'strPersonName', 'personName', 'socialName', 'strSocialName',
            'registration', 'matricula', 'strMatricula', 'cpf', 'pis', 'strPis',
            'saldoBancoHoras', 'strSaldoBancoHoras', 'bancoHoras', 'saldo',
            'minutesBank', 'balance', 'totalBancoHoras', 'strBanco', 'strSaldo',
            'departmentName', 'idDepartment',
            'roleName', 'idPersonRole',
            'excluded',
            'idPerson', 'id_funcionario',
        ]);
        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = array_intersect_key($row, $keep);
        }

        return $out;
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return list<array<string, mixed>>
     */
    /**
     * Lista JSON (0..n-1) com chaves inteiras ou string-numericas consecutivas.
     *
     * @param  array<int|string, mixed>  $arr
     */
    protected function isSequentialRowList(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }
        $expected = 0;
        foreach (array_keys($arr) as $k) {
            $i = is_int($k) ? $k : (is_string($k) && ctype_digit((string) $k) ? (int) $k : null);
            if ($i !== $expected) {
                return false;
            }
            $expected++;
        }

        return true;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    protected function mergePersonNestedIntoBankHourRow(array $row): array
    {
        $nestedKeys = ['person', 'Person', 'pessoa', 'Pessoa'];
        $lift = [
            'nome', 'name', 'strNome', 'strName', 'strPersonName', 'personName',
            'socialName', 'strSocialName',
            'registration', 'matricula', 'strMatricula', 'cpf', 'pis', 'strPis',
            'departmentName', 'idDepartment', 'roleName', 'idPersonRole',
        ];
        foreach ($nestedKeys as $nk) {
            if (! isset($row[$nk]) || ! is_array($row[$nk])) {
                continue;
            }
            $inner = $row[$nk];
            foreach ($lift as $f) {
                $empty = ! array_key_exists($f, $row) || $row[$f] === null || $row[$f] === '';
                if ($empty && isset($inner[$f]) && $inner[$f] !== null && $inner[$f] !== '') {
                    $row[$f] = $inner[$f];
                }
            }
            $idEmpty = ! array_key_exists('idPerson', $row) || $row['idPerson'] === null || $row['idPerson'] === '';
            if ($idEmpty && isset($inner['id']) && is_numeric($inner['id'])) {
                $row['idPerson'] = (int) $inner['id'];
            }
        }

        return $row;
    }

    /**
     * @param  array<int|string, mixed>  $json
     * @return list<array<string, mixed>>
     */
    protected function normalizeBankHoursRows(array $json, int $depth = 0): array
    {
        if ($depth > 20) {
            return [$json];
        }

        if ($json === []) {
            return [];
        }

        if ($this->isSequentialRowList($json)) {
            /** @var list<array<string, mixed>> $out */
            $out = [];
            foreach ($json as $row) {
                if (is_array($row)) {
                    $out[] = $row;
                }
            }

            return $out;
        }

        $listKeys = [
            'data', 'rows', 'items', 'results', 'list', 'persons', 'values',
            'personBankHours', 'PersonBankHours', 'd',
        ];
        foreach ($listKeys as $lk) {
            if (isset($json[$lk]) && is_array($json[$lk])) {
                return $this->normalizeBankHoursRows($json[$lk], $depth + 1);
            }
        }

        /** @var array<string, mixed> $json */
        return [$json];
    }

    /**
     * @param  array<string, mixed>  $listJson
     * @return list<int>
     */
    protected function extractPersonIdsFromListResponse(array $listJson): array
    {
        $rows = $listJson['data'] ?? $listJson;
        if (! is_array($rows)) {
            return [];
        }
        if ($rows !== [] && array_keys($rows) !== range(0, count($rows) - 1)) {
            $rows = [$rows];
        }
        $ids = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            if (isset($row['id']) && is_numeric($row['id'])) {
                $ids[] = (int) $row['id'];
            }
        }

        return $ids;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    public function massPersonShift(Company $company, ?User $user, array $items): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'POST',
            'customerdb/personshift.svc/insertlist',
            [
                'body' => $items,
                'auditAction' => 'rhid.personshift.insertlist',
            ],
        );

        return $this->decodeJson($r, 'personshift.insertlist');
    }

    /**
     * @return array<string, mixed>
     */
    protected function decodeJson(Response $response, string $context): array
    {
        if ($response->failed()) {
            throw RhidApiException::fromResponse($response, $context);
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RhidApiException('Resposta RHID invalida (nao JSON).', $response->status());
        }

        return $json;
    }
}
