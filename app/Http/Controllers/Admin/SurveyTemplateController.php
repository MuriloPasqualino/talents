<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyTemplate;
use App\Models\SurveyTemplateQuestion;
use App\Models\SurveyTemplateSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SurveyTemplateController extends Controller
{
    public function index(): Response
    {
        $templates = SurveyTemplate::query()
            ->withCount('sections')
            ->orderByDesc('id')
            ->paginate(20);

        return Inertia::render('Admin/SurveyTemplates/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/SurveyTemplates/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.description' => ['nullable', 'string'],
            'sections.*.questions' => ['required', 'array', 'min:1'],
            'sections.*.questions.*.body' => ['required', 'string'],
            'sections.*.questions.*.reverse_score' => ['boolean'],
            'sections.*.questions.*.weight' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'sections.*.questions.*.response_scale' => ['nullable', 'string', 'in:frequency,agreement'],
        ]);

        DB::transaction(function () use ($data) {
            $template = SurveyTemplate::create([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id(),
            ]);

            foreach ($data['sections'] as $sIndex => $section) {
                $sec = SurveyTemplateSection::create([
                    'survey_template_id' => $template->id,
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
        });

        return redirect()->route('admin.survey-templates.index')->with('success', 'Mapeamento criado.');
    }

    public function show(SurveyTemplate $surveyTemplate): Response
    {
        $surveyTemplate->load(['sections.questions']);

        return Inertia::render('Admin/SurveyTemplates/Show', [
            'template' => $surveyTemplate,
        ]);
    }

    public function edit(SurveyTemplate $surveyTemplate): Response
    {
        $surveyTemplate->load(['sections.questions']);

        $surveysWithCompletedResponses = Survey::query()
            ->where('survey_template_id', $surveyTemplate->id)
            ->whereHas('completedResponses')
            ->count();

        return Inertia::render('Admin/SurveyTemplates/Edit', [
            'template' => $surveyTemplate,
            'surveysWithCompletedResponses' => $surveysWithCompletedResponses,
        ]);
    }

    public function update(Request $request, SurveyTemplate $surveyTemplate): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.id' => ['nullable', 'integer'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.description' => ['nullable', 'string'],
            'sections.*.questions' => ['required', 'array', 'min:1'],
            'sections.*.questions.*.id' => ['nullable', 'integer'],
            'sections.*.questions.*.body' => ['required', 'string'],
            'sections.*.questions.*.reverse_score' => ['boolean'],
            'sections.*.questions.*.weight' => ['nullable', 'numeric', 'min:0.01', 'max:100'],
            'sections.*.questions.*.response_scale' => ['nullable', 'string', 'in:frequency,agreement'],
        ]);

        DB::transaction(function () use ($surveyTemplate, $data) {
            $surveyTemplate->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            $keptSectionIds = [];

            foreach ($data['sections'] as $sIndex => $section) {
                $sec = $this->resolveSection($surveyTemplate, $section);
                $sec->update([
                    'title' => $section['title'],
                    'description' => $section['description'] ?? null,
                    'sort_order' => $sIndex,
                ]);
                $keptSectionIds[] = $sec->id;

                $keptQuestionIds = [];
                foreach ($section['questions'] as $qIndex => $question) {
                    $q = $this->resolveQuestion($sec, $question);
                    $q->update([
                        'body' => $question['body'],
                        'reverse_score' => $question['reverse_score'] ?? false,
                        'weight' => isset($question['weight']) ? (float) $question['weight'] : 1.0,
                        'response_scale' => $question['response_scale'] ?? 'frequency',
                        'sort_order' => $qIndex,
                    ]);
                    $keptQuestionIds[] = $q->id;
                }

                $sec->questions()->whereNotIn('id', $keptQuestionIds)->delete();
            }

            $surveyTemplate->sections()->whereNotIn('id', $keptSectionIds)->delete();
        });

        return redirect()->route('admin.survey-templates.index')->with('success', 'Mapeamento atualizado.');
    }

    public function destroy(SurveyTemplate $surveyTemplate): RedirectResponse
    {
        $surveyTemplate->delete();

        return redirect()->route('admin.survey-templates.index')->with('success', 'Mapeamento removido.');
    }

    /**
     * @param  array<string, mixed>  $section
     */
    private function resolveSection(SurveyTemplate $template, array $section): SurveyTemplateSection
    {
        if (! empty($section['id'])) {
            $existing = SurveyTemplateSection::query()
                ->where('id', $section['id'])
                ->where('survey_template_id', $template->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return SurveyTemplateSection::create([
            'survey_template_id' => $template->id,
            'title' => $section['title'],
            'description' => $section['description'] ?? null,
            'sort_order' => 0,
        ]);
    }

    /**
     * @param  array<string, mixed>  $question
     */
    private function resolveQuestion(SurveyTemplateSection $section, array $question): SurveyTemplateQuestion
    {
        if (! empty($question['id'])) {
            $existing = SurveyTemplateQuestion::query()
                ->where('id', $question['id'])
                ->where('survey_template_section_id', $section->id)
                ->first();

            if ($existing) {
                return $existing;
            }
        }

        return SurveyTemplateQuestion::create([
            'survey_template_section_id' => $section->id,
            'body' => $question['body'],
            'reverse_score' => $question['reverse_score'] ?? false,
            'weight' => isset($question['weight']) ? (float) $question['weight'] : 1.0,
            'response_scale' => $question['response_scale'] ?? 'frequency',
            'sort_order' => 0,
        ]);
    }
}
