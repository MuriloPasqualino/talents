<?php

namespace App\Console\Commands;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Services\SurveyAnswerReconstructor;
use Illuminate\Console\Command;

class Nr1ReconstructAnswersCommand extends Command
{
    protected $signature = 'nr1:reconstruct-answers
                            {survey : ID da pesquisa}
                            {--force : Substitui respostas individuais já existentes}
                            {--dry-run : Apenas simula, sem gravar}
                            {--yes : Confirma sem perguntar (uso em produção)}';

    protected $description = 'Reconstrói survey_answers a partir de survey_responses (dados estimados, não originais)';

    public function handle(SurveyAnswerReconstructor $reconstructor): int
    {
        $survey = Survey::query()->find($this->argument('survey'));

        if (! $survey) {
            $this->error('Pesquisa não encontrada.');

            return self::FAILURE;
        }

        $existingAnswers = SurveyAnswer::query()
            ->whereIn('survey_response_id', $survey->completedResponses()->pluck('id'))
            ->count();

        $completedCount = $survey->completedResponses()->count();

        $this->info("Pesquisa [{$survey->id}] {$survey->title}");
        $this->line("  Respondentes concluídos: {$completedCount}");
        $this->line("  Respostas individuais atuais: {$existingAnswers}");

        if ($existingAnswers > 0 && ! $this->option('force')) {
            $this->error('Já existem respostas individuais. Use --force para substituir.');

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $questionCount = $survey->template()->with('sections.questions')->first()
                ?->sections->flatMap(fn ($s) => $s->questions)->count() ?? 0;
            $wouldCreate = $completedCount * $questionCount;
            $this->warn('Modo simulação — nada foi gravado.');
            $this->line("  Seriam criadas ~{$wouldCreate} linhas em survey_answers.");
            $this->warn('  ATENÇÃO: os valores serão ESTIMADOS, não as respostas reais dos participantes.');

            return self::SUCCESS;
        }

        if (! $this->option('yes') && ! $this->confirm('Os valores gerados são estimativas e NÃO substituem as respostas originais. Continuar?')) {
            $this->line('Operação cancelada.');

            return self::SUCCESS;
        }

        try {
            $result = $reconstructor->reconstruct($survey, (bool) $this->option('force'));
        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Reconstrução concluída.');
        $this->line("  Respondentes processados: {$result['responses']}");
        $this->line("  Respostas individuais criadas: {$result['answers_created']}");
        $this->warn('  A pesquisa foi marcada como answers_reconstructed_at — exiba o aviso no portal.');
        $this->line('  Resultados recalculados automaticamente.');

        return self::SUCCESS;
    }
}
