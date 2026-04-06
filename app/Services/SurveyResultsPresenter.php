<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyResult;

class SurveyResultsPresenter
{
    /**
     * Dados agregados da pesquisa no mesmo formato usado em Resultados (cliente).
     *
     * @return array<string, mixed>
     */
    public static function forSurvey(Survey $survey): array
    {
        $survey->loadMissing(['template.sections', 'company', 'insights']);

        $results = SurveyResult::query()
            ->where('survey_id', $survey->id)
            ->with(['section', 'department'])
            ->orderBy('survey_template_section_id')
            ->orderBy('department_id')
            ->get();

        $overall = $results->first(fn ($r) => $r->survey_template_section_id === null && $r->department_id === null);

        $bySection = $results->filter(fn ($r) => $r->survey_template_section_id !== null && $r->department_id === null)->values();

        $deptOveralls = $results
            ->filter(fn ($r) => $r->department_id !== null && $r->survey_template_section_id === null)
            ->values();

        $deptSectionResults = $results
            ->filter(fn ($r) => $r->department_id !== null && $r->survey_template_section_id !== null);

        $deptSectionsByDepartment = $deptSectionResults
            ->groupBy('department_id')
            ->map(function ($rows) {
                $first = $rows->first();

                return [
                    'department_id' => $first->department_id,
                    'department_name' => $first->department?->name ?? 'Setor #'.$first->department_id,
                    'sections' => $rows->values()->map(function ($r) {
                        return [
                            'id' => $r->id,
                            'survey_template_section_id' => $r->survey_template_section_id,
                            'average_score' => (float) $r->average_score,
                            'risk_level' => $r->risk_level,
                            'respondent_count' => $r->respondent_count,
                            'meta' => $r->meta,
                        ];
                    }),
                ];
            })
            ->values();

        return [
            'survey' => $survey,
            'overall' => $overall,
            'bySection' => $bySection,
            'deptOveralls' => $deptOveralls->map(function ($r) {
                return [
                    'id' => $r->id,
                    'average_score' => (float) $r->average_score,
                    'risk_level' => $r->risk_level,
                    'respondent_count' => $r->respondent_count,
                    'department_id' => $r->department_id,
                    'department_name' => $r->department?->name ?? 'Setor #'.$r->department_id,
                ];
            }),
            'deptSectionsByDepartment' => $deptSectionsByDepartment,
            'insights' => $survey->insights,
        ];
    }
}
