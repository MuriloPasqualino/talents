<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyInsight;
use App\Models\SurveyResult;

class InsightGenerator
{
    /** Benchmark médio de saúde estimado por segmento (0–100; pode vir de tabela futura) */
    private function benchmarkForSegment(?string $segment): float
    {
        $map = [
            'tecnologia' => 58,
            'saude' => 52,
            'educacao' => 55,
            'industria' => 55,
        ];

        if ($segment && isset($map[strtolower($segment)])) {
            return $map[strtolower($segment)];
        }

        return 55;
    }

    public function generateForSurvey(Survey $survey): void
    {
        $survey->insights()->delete();

        $survey->load('company');

        $overall = SurveyResult::query()
            ->where('survey_id', $survey->id)
            ->whereNull('survey_template_section_id')
            ->whereNull('department_id')
            ->first();

        if (! $overall) {
            return;
        }

        if ($overall->risk_level === 'red') {
            SurveyInsight::create([
                'survey_id' => $survey->id,
                'type' => 'alert',
                'message' => 'Indicador de saúde psicossocial geral baixo. Priorize ações imediatas com SESMT/RH e liderança.',
                'meta' => ['average_score' => $overall->average_score],
            ]);
        }

        $benchmark = $this->benchmarkForSegment($survey->company->segment);
        if ($overall->average_score < $benchmark - 5) {
            SurveyInsight::create([
                'survey_id' => $survey->id,
                'type' => 'benchmark',
                'message' => 'Atenção: a média de saúde está abaixo do benchmark estimado do segmento ('.$benchmark.' pontos).',
                'meta' => ['benchmark' => $benchmark, 'company' => $overall->average_score],
            ]);
        }

        $sections = SurveyResult::query()
            ->where('survey_id', $survey->id)
            ->whereNotNull('survey_template_section_id')
            ->whereNull('department_id')
            ->get();

        foreach ($sections as $row) {
            if ($row->risk_level === 'red') {
                $title = $row->meta['section_title'] ?? 'Dimensão';
                SurveyInsight::create([
                    'survey_id' => $survey->id,
                    'type' => 'alert',
                    'message' => 'Área crítica: '.$title.'. Inclua medidas específicas no PGR.',
                    'meta' => ['section_id' => $row->survey_template_section_id],
                ]);
            }
        }

        $previous = Survey::query()
            ->where('company_id', $survey->company_id)
            ->where('id', '<', $survey->id)
            ->orderByDesc('id')
            ->first();

        if ($previous) {
            $prevOverall = SurveyResult::query()
                ->where('survey_id', $previous->id)
                ->whereNull('survey_template_section_id')
                ->whereNull('department_id')
                ->first();
            if ($prevOverall && $overall->average_score > $prevOverall->average_score + 3) {
                SurveyInsight::create([
                    'survey_id' => $survey->id,
                    'type' => 'trend',
                    'message' => 'Tendência positiva: a média geral de saúde melhorou em relação à campanha anterior.',
                    'meta' => ['previous' => $prevOverall->average_score, 'current' => $overall->average_score],
                ]);
            }
            if ($prevOverall && $overall->average_score < $prevOverall->average_score - 3) {
                SurveyInsight::create([
                    'survey_id' => $survey->id,
                    'type' => 'trend',
                    'message' => 'Atenção: os indicadores de saúde pioraram em relação à campanha anterior.',
                    'meta' => ['previous' => $prevOverall->average_score, 'current' => $overall->average_score],
                ]);
            }
        }
    }
}
