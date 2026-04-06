<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GenerateAiAnalysisJob;
use App\Models\ActionPlan;
use App\Models\ActionPlanItem;
use App\Models\AiAnalysis;
use App\Models\AiSetting;
use App\Models\Company;
use App\Models\Survey;
use App\Services\Nr1AiAnalyzer;
use App\Services\SurveyResultsPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;
use Inertia\Response;

class ActionPlanAdminController extends Controller
{
    private function assertSurveyBelongsToCompany(Company $company, Survey $survey): void
    {
        abort_unless($survey->company_id === $company->id, 404);
    }

    public function edit(Company $company, Survey $survey): Response
    {
        $this->assertSurveyBelongsToCompany($company, $survey);

        $presented = SurveyResultsPresenter::forSurvey($survey);

        $aiSetting = AiSetting::current();
        $aiEnabled = $aiSetting
            && $aiSetting->is_enabled
            && $aiSetting->safeApiKey() !== null;

        $latestAi = AiAnalysis::query()
            ->where('survey_id', $survey->id)
            ->where('type', Nr1AiAnalyzer::TYPE_NR1_GUIDANCE)
            ->latest()
            ->first();

        $aiAnalysisPending = Cache::has('ai_job_pending_'.$survey->id);

        $plan = ActionPlan::query()
            ->where('company_id', $company->id)
            ->where('survey_id', $survey->id)
            ->with('items')
            ->first();

        $items = $plan?->items->map(fn (ActionPlanItem $i) => [
            'id' => $i->id,
            'title' => $i->title,
            'description' => $i->description ?? '',
        ])->values()->all() ?? [];

        return Inertia::render('Admin/ActionPlan/Edit', array_merge($presented, [
            'company' => $company->only(['id', 'name']),
            'plan' => $plan ? [
                'id' => $plan->id,
                'admin_published_at' => $plan->admin_published_at?->format('d/m/Y H:i'),
            ] : null,
            'items' => $items,
            'aiEnabled' => $aiEnabled,
            'aiAnalysis' => $latestAi ? [
                'content' => $latestAi->content,
            ] : null,
            'aiAnalysisPending' => $aiAnalysisPending,
            // URL explícita: evita RouteNotFoundException quando há cache de rotas antigo sem este nome.
            'aiGeneratePostUrl' => url('/admin/companies/'.$company->getKey().'/surveys/'.$survey->getKey().'/ai-analysis'),
        ]));
    }

    public function generateAiAnalysis(Request $request, Company $company, Survey $survey): RedirectResponse
    {
        $this->assertSurveyBelongsToCompany($company, $survey);

        $setting = AiSetting::current();
        if (! $setting || ! $setting->is_enabled || $setting->safeApiKey() === null) {
            return back()->with('error', 'A análise por IA não está disponível. Configure a Mia em Configurações.');
        }

        if (! $survey->results()->exists()) {
            return back()->with('error', 'Não há resultados calculados para esta pesquisa.');
        }

        $rateKey = 'admin-ai-analysis:'.$survey->id.':'.$request->user()->id;
        if (RateLimiter::tooManyAttempts($rateKey, 8)) {
            return back()->with('error', 'Limite de gerações por hora atingido. Tente mais tarde.');
        }

        $pendingKey = 'ai_job_pending_'.$survey->id;
        if (Cache::has($pendingKey)) {
            return back()->with('info', 'Já existe uma análise em processamento. Atualize a página em instantes.');
        }

        Cache::put($pendingKey, true, now()->addMinutes(15));
        RateLimiter::hit($rateKey, 3600);

        GenerateAiAnalysisJob::dispatch($survey->id, (int) $request->user()->id);

        return redirect()
            ->route('admin.companies.surveys.action-plan.edit', [$company, $survey])
            ->with('success', 'Análise solicitada. Em alguns segundos atualize a página para ver o texto da Mia.');
    }

    public function update(Request $request, Company $company, Survey $survey): RedirectResponse
    {
        $this->assertSurveyBelongsToCompany($company, $survey);

        $data = $request->validate([
            'items' => ['present', 'array'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($company, $survey, $data) {
            $plan = ActionPlan::query()->firstOrCreate(
                [
                    'company_id' => $company->id,
                    'survey_id' => $survey->id,
                ],
                ['status' => 'open']
            );

            $plan->items()->delete();

            foreach ($data['items'] as $index => $row) {
                ActionPlanItem::create([
                    'action_plan_id' => $plan->id,
                    'title' => $row['title'],
                    'description' => $row['description'] ?? null,
                    'status' => 'pending',
                    'sort_order' => $index,
                ]);
            }

            if (count($data['items']) > 0) {
                $plan->update(['admin_published_at' => now()]);
            } else {
                $plan->update(['admin_published_at' => null]);
            }
        });

        return redirect()
            ->route('admin.companies.surveys.action-plan.edit', [$company, $survey])
            ->with('success', 'Plano de ação atualizado e disponibilizado para a empresa quando houver itens.');
    }
}
