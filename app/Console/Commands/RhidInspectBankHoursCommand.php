<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\Rhid\RhidComplianceService;
use App\Support\RhidBankBalanceFormat;
use Illuminate\Console\Command;
use ReflectionMethod;

class RhidInspectBankHoursCommand extends Command
{
    protected $signature = 'rhid:inspect-bank-hours
                            {--company= : ID da empresa com RHID configurado (obrigatório)}
                            {--date=20260413 : Data YYYYMMDD (ex.: 20260413 = 13/04/2026)}
                            {--cpf= : Filtrar colaborador por CPF (somente dígitos ou como veio na API)}
                            {--name= : Filtrar por trecho do nome (case insensitive)}
                            {--espelho-hhmm= : Saldo exibido no espelho RHID no mesmo dia (ex.: -06:05) para comparar}';

    protected $description = 'Consulta person_banco_horas na API RHID e mostra valores brutos vs formatados (HH:mm), opcionalmente comparando com o espelho.';

    public function handle(RhidComplianceService $compliance): int
    {
        $companyId = $this->option('company');
        if ($companyId === null || $companyId === '') {
            $this->error('Informe --company=ID da empresa.');

            return self::FAILURE;
        }

        $company = Company::query()->find((int) $companyId);
        if ($company === null) {
            $this->error('Empresa não encontrada.');

            return self::FAILURE;
        }

        if (! $company->rhidConfigured()) {
            $this->error('RHID não configurado para esta empresa (rhid_email / rhid_password).');

            return self::FAILURE;
        }

        $date = (string) $this->option('date');
        $cpfFilter = $this->normalizedDigits((string) $this->option('cpf'));
        $nameFilter = trim((string) $this->option('name'));
        $espelhoRaw = trim((string) $this->option('espelho-hhmm'));

        $this->info(sprintf('Empresa: %s (id=%s)', $company->name, $company->id));
        $this->line('Data (query): '.$date);
        $this->newLine();

        $query = ['date' => $date];

        try {
            $processedRows = $compliance->personBankHours($company, null, $query);
        } catch (\Throwable $e) {
            $this->error('Falha na API RHID: '.$e->getMessage());

            return self::FAILURE;
        }

        $normalize = new ReflectionMethod(RhidComplianceService::class, 'normalizeBankHoursRows');
        $normalize->setAccessible(true);
        $merge = new ReflectionMethod(RhidComplianceService::class, 'mergePersonNestedIntoBankHourRow');
        $merge->setAccessible(true);
        $canonicalize = new ReflectionMethod(RhidComplianceService::class, 'canonicalizeRhidBankHourBalanceFields');
        $canonicalize->setAccessible(true);

        $rawJson = app(\App\Services\Rhid\RhidClient::class)->request(
            $company,
            null,
            'GET',
            'customerdb/person.svc/person_banco_horas',
            [
                'query' => $query,
                'auditAction' => 'rhid.inspect_bank_hours',
            ],
        )->json();

        if (! is_array($rawJson)) {
            $this->error('Resposta RHID não é JSON objeto/array.');

            return self::FAILURE;
        }

        $rawRows = $normalize->invoke($compliance, $rawJson, 0);

        $matches = [];
        foreach ($rawRows as $idx => $rawRow) {
            if (! is_array($rawRow)) {
                continue;
            }
            $name = (string) ($rawRow['name'] ?? $rawRow['nome'] ?? '');
            $cpf = $this->normalizedDigits((string) ($rawRow['cpf'] ?? ''));
            if ($cpfFilter !== '' && $cpf !== $cpfFilter) {
                continue;
            }
            if ($nameFilter !== '' && stripos($name, $nameFilter) === false) {
                continue;
            }
            $matches[] = ['index' => $idx, 'raw' => $rawRow];
        }

        if ($matches === []) {
            $this->warn('Nenhuma linha encontrada com os filtros. Total de linhas na resposta: '.count($rawRows));

            return self::SUCCESS;
        }

        $espelhoMin = $espelhoRaw !== '' ? RhidBankBalanceFormat::hhMmToSignedMinutes($espelhoRaw) : null;
        if ($espelhoRaw !== '' && $espelhoMin === null) {
            $this->warn('Não foi possível interpretar --espelho-hhmm='.$espelhoRaw.' (use formato -HH:mm ou HH:mm).');
        }

        foreach ($matches as $item) {
            $raw = $item['raw'];
            $merged = $merge->invoke($compliance, $raw, 'inspect');
            $afterCanon = $canonicalize->invoke($compliance, $merged);

            $nome = $afterCanon['name'] ?? $afterCanon['nome'] ?? '?';
            $cpfOut = $afterCanon['cpf'] ?? '—';
            $saldoRaw = $raw['saldoBancoHoras'] ?? null;
            $strRaw = $raw['strSaldoBancoHoras'] ?? $raw['StrSaldoBancoHoras'] ?? null;
            $saldoCanon = $afterCanon['saldoBancoHoras'] ?? null;
            $hasPerson = isset($raw['person']) || isset($raw['Person']);

            $this->line('<fg=cyan>=== '.$nome.' ===</>');
            $this->line('CPF: '.$cpfOut);
            $this->line('Índice na lista: '.$item['index']);
            $this->line('Objeto person aninhado: '.($hasPerson ? 'sim' : 'não'));
            $this->newLine();

            $this->line('Chaves na linha BRUTA (antes do merge Talents):');
            $this->line(json_encode(array_keys($raw), JSON_UNESCAPED_UNICODE));
            $this->newLine();

            $this->line('Valores relevantes (bruto):');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['saldoBancoHoras (API)', $this->scalar($saldoRaw)],
                    ['strSaldoBancoHoras (API)', $this->scalar($strRaw)],
                ],
            );

            $this->line('Após merge + canonicalize (Talents):');
            $this->table(
                ['Campo', 'Valor'],
                [
                    ['saldoBancoHoras', $this->scalar($saldoCanon)],
                    ['strSaldoBancoHoras', $this->scalar($afterCanon['strSaldoBancoHoras'] ?? null)],
                ],
            );

            if (is_numeric($saldoCanon)) {
                $hhmm = RhidBankBalanceFormat::minutesToHhMm((float) $saldoCanon);
                $this->info('Exibição HH:mm (minutos → como na UI Talents): '.$hhmm);
            }

            if (is_numeric($saldoRaw)) {
                $hhmmRaw = RhidBankBalanceFormat::minutesToHhMm((float) $saldoRaw);
                $this->line('Se apenas saldoBancoHoras bruto for minutos: '.$hhmmRaw);
            }

            if ($espelhoMin !== null && is_numeric($saldoCanon)) {
                $apiMin = (int) round((float) $saldoCanon);
                $delta = $apiMin - $espelhoMin;
                $this->newLine();
                $this->line('Comparação com espelho RHID (--espelho-hhmm):');
                $this->table(
                    ['', 'Minutos', 'HH:mm'],
                    [
                        ['API (após pipeline)', (string) $apiMin, RhidBankBalanceFormat::minutesToHhMm($apiMin)],
                        ['Espelho (informado)', (string) $espelhoMin, RhidBankBalanceFormat::minutesToHhMm($espelhoMin)],
                        ['Delta (API − espelho)', (string) $delta, '—'],
                    ],
                );
                if ($delta !== 0) {
                    $this->warn('Os minutos da API não coincidem com o espelho. Possíveis causas: outro campo na API, outro período/regra, ou saldoBancoHoras não é o mesmo conceito do “Banco Saldo” da tela.');
                } else {
                    $this->info('API e espelho batem em minutos.');
                }
            }

            $this->newLine();
            $this->line('JSON bruto da linha (amostra):');
            $this->line(json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $this->newLine();
        }

        $this->line('Total de linhas processadas pela API nesta consulta: '.count($processedRows));

        return self::SUCCESS;
    }

    private function normalizedDigits(string $s): string
    {
        return preg_replace('/\D/', '', $s) ?? '';
    }

    private function scalar(mixed $v): string
    {
        if ($v === null) {
            return '—';
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_array($v)) {
            return json_encode($v, JSON_UNESCAPED_UNICODE);
        }

        return (string) $v;
    }
}
