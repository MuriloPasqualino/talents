<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActionPlanItem;
use App\Models\Survey;
use App\Services\ActionPlanGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActionPlanController extends Controller
{
    private function companyId(Request $request): int
    {
        return (int) $request->user()->company_id;
    }

    private function findSurvey(Request $request, Survey $survey): Survey
    {
        abort_unless($survey->company_id === $this->companyId($request), 404);

        return $survey;
    }

    public function show(Request $request, Survey $survey): Response
    {
        $survey = $this->findSurvey($request, $survey);

        $plan = $survey->actionPlans()->with('items')->latest()->first();

        return Inertia::render('Client/Surveys/ActionPlan', [
            'survey' => $survey,
            'plan' => $plan,
        ]);
    }

    public function generate(Request $request, Survey $survey, ActionPlanGenerator $generator): RedirectResponse
    {
        $survey = $this->findSurvey($request, $survey);

        $generator->generate($survey);

        return redirect()->route('client.surveys.action-plan', $survey)->with('success', 'Plano de ação gerado.');
    }

    public function updateItem(Request $request, ActionPlanItem $item): RedirectResponse
    {
        $plan = $item->actionPlan;
        abort_unless($plan->company_id === $this->companyId($request), 403);

        $data = $request->validate([
            'responsible_name' => ['nullable', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', 'in:pending,in_progress,done'],
        ]);

        $item->update($data);

        return back()->with('success', 'Item atualizado.');
    }
}
