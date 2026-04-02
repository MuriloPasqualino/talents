<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\SurveyResult;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $companiesCount = Company::query()->count();
        $activeCompanies = Company::query()->where('is_active', true)->count();

        $surveysTotal = Survey::query()->count();
        $responsesTotal = SurveyResponse::query()->whereNotNull('completed_at')->count();

        $riskBySegment = SurveyResult::query()
            ->whereNull('survey_template_section_id')
            ->whereNull('department_id')
            ->join('surveys', 'surveys.id', '=', 'survey_results.survey_id')
            ->join('companies', 'companies.id', '=', 'surveys.company_id')
            ->whereNotNull('companies.segment')
            ->select('companies.segment', DB::raw('avg(survey_results.average_score) as avg_score'))
            ->groupBy('companies.segment')
            ->orderByDesc('avg_score')
            ->get();

        $criticalCompanies = Company::query()
            ->where('is_active', true)
            ->get()
            ->filter(function ($c) {
                $lastSurvey = Survey::query()->where('company_id', $c->id)->orderByDesc('id')->first();
                if (! $lastSurvey) {
                    return false;
                }
                $r = SurveyResult::query()
                    ->where('survey_id', $lastSurvey->id)
                    ->whereNull('survey_template_section_id')
                    ->whereNull('department_id')
                    ->first();

                return $r && $r->risk_level === 'red';
            })
            ->take(10)
            ->values();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'companies_total' => $companiesCount,
                'companies_active' => $activeCompanies,
                'surveys_total' => $surveysTotal,
                'responses_completed' => $responsesTotal,
            ],
            'riskBySegment' => $riskBySegment,
            'criticalCompanies' => $criticalCompanies->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'segment' => $c->segment,
            ]),
        ]);
    }
}
