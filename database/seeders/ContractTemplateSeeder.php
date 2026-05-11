<?php

namespace Database\Seeders;

use App\Models\CommercialContractTemplate;
use App\Services\Commercial\DocxToHtmlService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ContractTemplateSeeder extends Seeder
{
    /** @var array<int, array{name: string, files: array<int, string>, hints?: array<int, string>}> Ficheiros em database/seed_data/contract_templates (fora de storage/ por causa do volume Docker). */
    private const DEFINITIONS = [
        [
            'name' => 'Consultoria - Padrão Talents',
            'files' => [
                'TALENTS - CONSULTORIA - NOVO.docx',
                'TALENTS - CONSULTORIA - NOVO .docx',
            ],
        ],
        [
            'name' => 'Contratação de Talentos - Padrão Talents',
            'files' => [
                'TALENTS - CONTRATACAO DE TALENTOS - NOVO.docx',
                'TALENTS - CONTRATAÇAO DE TALENTOS - NOVO.docx',
                'TALENTS - CONTRATAÇÃO DE TALENTOS - NOVO.docx',
            ],
            'hints' => ['talentos', 'contrat'],
        ],
        [
            'name' => 'Palestra - Padrão Talents',
            'files' => [
                'TALENTS - PALESTRA - NOVO.docx',
            ],
        ],
    ];

    public function run(): void
    {
        $base = database_path('seed_data/contract_templates');

        foreach (self::DEFINITIONS as $def) {
            $path = $this->resolveFirstExistingPath($base, $def['files']);
            if ($path === null && ! empty($def['hints'] ?? [])) {
                $path = $this->resolveByFilenameHints($base, $def['hints']);
            }
            if ($path === null) {
                Log::warning('[ContractTemplateSeeder] Arquivo ausente; modelo não criado.', [
                    'name' => $def['name'],
                    'tried' => $def['files'],
                ]);

                continue;
            }

            try {
                $html = DocxToHtmlService::extractFromAbsolutePath($path);
                [$html, $applied] = $this->applyPlaceholderHeuristics($html);

                CommercialContractTemplate::query()->updateOrCreate(
                    ['name' => $def['name']],
                    [
                        'source_type' => 'html',
                        'body_html' => $html,
                        'docx_path' => null,
                        'is_active' => true,
                    ],
                );

                Log::info('[ContractTemplateSeeder] Modelo atualizado.', [
                    'name' => $def['name'],
                    'source_file' => basename($path),
                    'placeholders_applied' => $applied,
                ]);
            } catch (\Throwable $e) {
                Log::error('[ContractTemplateSeeder] Falha ao processar modelo.', [
                    'name' => $def['name'],
                    'file' => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param  array<int, string>  $filenames
     */
    private function resolveFirstExistingPath(string $base, array $filenames): ?string
    {
        foreach ($filenames as $name) {
            $full = $base.DIRECTORY_SEPARATOR.$name;
            if (is_file($full) && is_readable($full)) {
                return $full;
            }
        }

        return null;
    }

    /**
     * Localiza .docx quando o nome no disco difere (acentos, encoding).
     *
     * @param  array<int, string>  $hints  Substrings que devem aparecer no nome (case-insensitive)
     */
    private function resolveByFilenameHints(string $base, array $hints): ?string
    {
        foreach (scandir($base) ?: [] as $f) {
            if (! str_ends_with(strtolower($f), '.docx')) {
                continue;
            }
            $lower = mb_strtolower($f);
            $ok = true;
            foreach ($hints as $h) {
                if (! str_contains($lower, mb_strtolower($h))) {
                    $ok = false;
                    break;
                }
            }
            if (! $ok) {
                continue;
            }
            $full = $base.DIRECTORY_SEPARATOR.$f;
            if (is_readable($full)) {
                return $full;
            }
        }

        return null;
    }

    /**
     * @return array{0: string, 1: array<int, string>}
     */
    private function applyPlaceholderHeuristics(string $html): array
    {
        $applied = [];

        // Datas em branco comuns em contratos
        $before = $html;
        $html = str_replace(
            ['__/__/____', '__/___/____', '____/____/________'],
            '{{data_hoje}}',
            $html,
        );
        if ($html !== $before) {
            $applied[] = 'data_hoje (linhas em branco)';
        }

        // Primeiro valor em R$ (formato brasileiro) → honorário / total da proposta
        $before = $html;
        $html = preg_replace(
            '/R\$\s*\d{1,3}(?:\.\d{3})*,\d{2}/u',
            '{{total_reais}}',
            $html,
            1,
            $count,
        );
        if ($count > 0) {
            $applied[] = 'total_reais (1º valor R$)';
        }

        // Trecho entre parênteses com "reais" (valor por extenso)
        $before = $html;
        $html = preg_replace(
            '/\([^)]{15,220}\breais?\b[^)]*\)/ui',
            '({{total_extenso}})',
            $html,
            1,
            $count,
        );
        if ($count > 0) {
            $applied[] = 'total_extenso (1º parênteses com "reais")';
        }

        // CNPJs: ímpar → cliente, par → empresa (ordem típica em contratos bilaterais)
        $cnpjN = 0;
        $before = $html;
        $html = preg_replace_callback(
            '/\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2}/',
            function () use (&$cnpjN) {
                $cnpjN++;

                return $cnpjN % 2 === 1 ? '{{cliente_cnpj}}' : '{{empresa_cnpj}}';
            },
            $html,
        );
        if ($html !== $before) {
            $applied[] = "cnpj alternado ({$cnpjN} ocorrências)";
        }

        // E-mails: ímpar → cliente, par → empresa
        $mailN = 0;
        $before = $html;
        $html = preg_replace_callback(
            '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
            function () use (&$mailN) {
                $mailN++;

                return $mailN % 2 === 1 ? '{{cliente_email}}' : '{{empresa_email}}';
            },
            $html,
        );
        if ($html !== $before) {
            $applied[] = "email alternado ({$mailN} ocorrências)";
        }

        // Telefones com DDD (padrão simples)
        $telN = 0;
        $before = $html;
        $html = preg_replace_callback(
            '/\(?\d{2}\)?\s*\d{4,5}-?\d{4}/',
            function () use (&$telN) {
                $telN++;
                if ($telN === 1) {
                    return '{{cliente_telefone}}';
                }
                if ($telN === 2) {
                    return '{{empresa_telefone}}';
                }

                return '{{empresa_telefone}}';
            },
            $html,
        );
        if ($html !== $before) {
            $applied[] = "telefone ({$telN} ocorrências)";
        }

        // Prazo em dias (ex.: "no prazo de 30 dias")
        $before = $html;
        $html = preg_replace(
            '/\bno\s+prazo\s+de\s+(\d{1,4})\s+dias\b/ui',
            'no prazo de {{prazo_dias}} dias',
            $html,
            -1,
            $count,
        );
        if ($count > 0) {
            $applied[] = 'prazo_dias (no prazo de X dias)';
        }

        return [$html, $applied];
    }
}
