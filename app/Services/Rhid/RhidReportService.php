<?php

namespace App\Services\Rhid;

use App\Exceptions\RhidApiException;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Client\Response;

class RhidReportService
{
    public function __construct(
        private RhidClient $client,
    ) {}

    /**
     * Inicia geracao de Cartao ou Espelho (POST report.svc/ponto).
     *
     * @param  array<string, mixed>  $body
     * @return array{guid: string, numPeople?: int|null, error?: string|null}
     */
    public function startPontoReport(Company $company, ?User $user, array $body): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'POST',
            'report.svc/ponto',
            [
                'body' => $body,
                'timeout' => (int) config('rhid.report_timeout'),
                'auditAction' => 'rhid.report.ponto.start',
            ],
        );

        return $this->decodeStartResponse($r);
    }

    /**
     * @return array<string, mixed>
     */
    public function guidStatus(Company $company, ?User $user, string $guid): array
    {
        $r = $this->client->request(
            $company,
            $user,
            'GET',
            'customerdb/notify.svc/specificGuid/',
            [
                'query' => ['guid' => $guid],
                'auditAction' => 'rhid.report.guid_status',
            ],
        );

        if ($r->failed()) {
            throw RhidApiException::fromResponse($r, 'guid_status');
        }

        $json = $r->json();
        if (! is_array($json)) {
            throw new RhidApiException('Resposta invalida ao consultar GUID.', $r->status());
        }

        return $json;
    }

    /**
     * Baixa arquivo gerado (PDF/CSV/HTML).
     */
    public function downloadSaveFile(Company $company, ?User $user, string $format, string $guid): Response
    {
        return $this->client->request(
            $company,
            $user,
            'POST',
            'customerdb/notify.svc/save_file/',
            [
                'query' => [
                    'format' => $format,
                    'guid' => $guid,
                ],
                'as_json' => false,
                'expect_json' => false,
                'accept' => '*/*',
                'timeout' => (int) config('rhid.report_timeout'),
                'auditAction' => 'rhid.report.save_file',
            ],
        );
    }

    /**
     * Exporta AFD (portaria 1510 ou 671).
     *
     * @param  array<string, mixed>  $query  tipo, ini, fim, idCompany, etc.
     */
    public function exportAfd(Company $company, ?User $user, array $query, string $rawJsonBody = '[99000001]'): Response
    {
        return $this->client->request(
            $company,
            $user,
            'POST',
            'report.svc/exporta_arquivo/',
            [
                'query' => $query,
                'raw_body' => $rawJsonBody,
                'content_type' => 'application/json',
                'as_json' => false,
                'expect_json' => false,
                'accept' => '*/*',
                'timeout' => (int) config('rhid.report_timeout'),
                'headers' => [
                    'Host' => 'www.rhid.com.br',
                    'Origin' => 'https://www.rhid.com.br',
                    'Referer' => 'https://www.rhid.com.br/v2',
                ],
                'auditAction' => 'rhid.afd.export',
            ],
        );
    }

    /**
     * @return array{guid: string, numPeople?: int|null, error?: string|null}
     */
    protected function decodeStartResponse(Response $response): array
    {
        if ($response->failed()) {
            throw RhidApiException::fromResponse($response, 'report.ponto');
        }

        $json = $response->json();
        if (! is_array($json)) {
            throw new RhidApiException('Resposta invalida ao iniciar relatorio RHID (nao JSON).', $response->status());
        }

        $guid = $this->extractPontoStartGuid($json);
        if ($guid === null) {
            $apiErr = $json['error'] ?? $json['Error'] ?? null;
            $suffix = is_string($apiErr) && $apiErr !== '' ? ' '.$apiErr : '';

            throw new RhidApiException(
                'Resposta sem GUID ao iniciar relatorio RHID.'.$suffix,
                $response->status(),
                $json,
            );
        }

        return [
            'guid' => $guid,
            'numPeople' => $json['numPeople'] ?? $json['NumPeople'] ?? null,
            'error' => $json['error'] ?? $json['Error'] ?? null,
        ];
    }

    /**
     * O endpoint report.svc/ponto pode serializar em camelCase (guid) ou PascalCase (Guid).
     *
     * @param  array<string, mixed>  $json
     */
    protected function extractPontoStartGuid(array $json): ?string
    {
        foreach (['guid', 'Guid', 'GUID'] as $key) {
            if (! isset($json[$key])) {
                continue;
            }
            $v = $json[$key];
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }

        if (isset($json['d']) && is_array($json['d'])) {
            return $this->extractPontoStartGuid($json['d']);
        }

        return null;
    }
}
