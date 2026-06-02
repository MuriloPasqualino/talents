<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Nr1DiagnosticCommand extends Command
{
    protected $signature = 'nr1:diagnostic';

    protected $description = 'Diagnóstico do estado das pesquisas NR-1 e respostas no banco';

    public function handle(): int
    {
        $this->info('=== TEMPLATES DE PESQUISA ===');
        $templates = DB::table('survey_templates')->get(['id', 'title']);
        foreach ($templates as $t) {
            $qtdSecoes = DB::table('survey_template_sections')->where('survey_template_id', $t->id)->count();
            $qtdPerguntas = DB::table('survey_template_questions')
                ->join('survey_template_sections', 'survey_template_sections.id', '=', 'survey_template_questions.survey_template_section_id')
                ->where('survey_template_sections.survey_template_id', $t->id)
                ->count();
            $this->line("  [{$t->id}] {$t->title} — {$qtdSecoes} seções, {$qtdPerguntas} perguntas");
        }

        $this->newLine();
        $this->info('=== PESQUISAS (SURVEYS) ===');
        $surveys = DB::table('surveys')
            ->join('companies', 'companies.id', '=', 'surveys.company_id')
            ->get(['surveys.id', 'surveys.title', 'companies.name as empresa', 'surveys.survey_template_id', 'surveys.status']);
        foreach ($surveys as $s) {
            $reconstructed = DB::table('surveys')->where('id', $s->id)->value('answers_reconstructed_at');
            $flag = $reconstructed ? ' | RESPOSTAS RECONSTRUÍDAS' : '';
            $this->line("  [{$s->id}] {$s->title} | Empresa: {$s->empresa} | template_id={$s->survey_template_id} | status={$s->status}{$flag}");
        }

        $this->newLine();
        $this->info('=== RESPOSTAS POR PESQUISA ===');
        $respostas = DB::table('survey_responses')
            ->selectRaw('survey_id, COUNT(*) as total, SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completas')
            ->groupBy('survey_id')
            ->get();

        if ($respostas->isEmpty()) {
            $this->warn('  Nenhuma resposta encontrada!');
        } else {
            foreach ($respostas as $r) {
                $this->line("  survey_id={$r->survey_id} | total={$r->total} | completas={$r->completas}");
            }
        }

        $this->newLine();
        $this->info('=== RESPOSTAS INDIVIDUAIS (survey_answers) ===');
        $totalAnswers = DB::table('survey_answers')->count();

        if ($totalAnswers === 0) {
            $this->error('  NENHUMA resposta individual (survey_answers) encontrada no banco!');
            $this->warn('  As respostas foram apagadas em cascata quando o template foi editado.');
            $this->warn('  A recuperação depende de um backup do banco de dados.');
        } else {
            $this->info("  {$totalAnswers} respostas individuais encontradas.");

            $answersBySurvey = DB::table('survey_answers')
                ->join('survey_responses', 'survey_responses.id', '=', 'survey_answers.survey_response_id')
                ->selectRaw('survey_responses.survey_id, COUNT(*) as total_answers, COUNT(DISTINCT survey_responses.id) as respondentes')
                ->groupBy('survey_responses.survey_id')
                ->get();

            foreach ($answersBySurvey as $r) {
                $this->line("  survey_id={$r->survey_id} | respostas={$r->total_answers} | respondentes={$r->respondentes}");
            }
        }

        $this->newLine();
        $this->info('=== RESULTADOS CALCULADOS (survey_results) ===');
        $results = DB::table('survey_results')
            ->selectRaw('survey_id, COUNT(*) as total')
            ->groupBy('survey_id')
            ->get();
        if ($results->isEmpty()) {
            $this->warn('  Nenhum resultado calculado encontrado.');
        } else {
            foreach ($results as $r) {
                $this->line("  survey_id={$r->survey_id} | linhas={$r->total}");
            }
        }

        $this->newLine();
        $this->info('=== VERIFICAÇÃO DE ORPHANED ANSWERS ===');
        $orphaned = DB::table('survey_answers')
            ->leftJoin('survey_template_questions', 'survey_template_questions.id', '=', 'survey_answers.survey_template_question_id')
            ->whereNull('survey_template_questions.id')
            ->count();

        if ($orphaned > 0) {
            $this->warn("  {$orphaned} respostas apontam para perguntas que não existem mais (órfãs).");
        } else {
            $this->line('  Nenhuma resposta órfã encontrada (todas as respostas têm pergunta válida).');
        }

        return self::SUCCESS;
    }
}
