<?php

namespace App\Services;

use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\SurveyTemplateSection;
use Illuminate\Support\Facades\Auth;

class SurveyTemplateVersioningService
{
    public function shouldFork(SurveyTemplate $template): bool
    {
        return Survey::query()
            ->where('survey_template_id', $template->id)
            ->whereHas('completedResponses')
            ->exists();
    }

    /**
     * Cria nova versão do mapeamento sem alterar o original (preserva respostas históricas).
     *
     * @param  array<string, mixed>  $data
     */
    public function createFork(SurveyTemplate $original, array $data): SurveyTemplate
    {
        $fork = SurveyTemplate::create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => Auth::id(),
            'forked_from_id' => $original->id,
        ]);

        foreach ($data['sections'] as $sIndex => $section) {
            $sec = SurveyTemplateSection::create([
                'survey_template_id' => $fork->id,
                'title' => $section['title'],
                'description' => $section['description'] ?? null,
                'sort_order' => $sIndex,
            ]);

            foreach ($section['questions'] as $qIndex => $question) {
                SurveyTemplateQuestion::create([
                    'survey_template_section_id' => $sec->id,
                    'body' => $question['body'],
                    'reverse_score' => $question['reverse_score'] ?? false,
                    'weight' => isset($question['weight']) ? (float) $question['weight'] : 1.0,
                    'response_scale' => $question['response_scale'] ?? 'frequency',
                    'sort_order' => $qIndex,
                ]);
            }
        }

        return $fork->load(['sections.questions']);
    }
}
