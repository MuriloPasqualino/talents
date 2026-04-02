<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Survey;
use App\Models\SurveyResult;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $companyId = $request->user()->company_id;

        $activeSurveys = Survey::query()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->count();

        $lastSurvey = Survey::query()
            ->where('company_id', $companyId)
            ->with('template')
            ->orderByDesc('id')
            ->first();

        $overall = null;
        if ($lastSurvey) {
            $overall = SurveyResult::query()
                ->where('survey_id', $lastSurvey->id)
                ->whereNull('survey_template_section_id')
                ->whereNull('department_id')
                ->first();
        }

        $company = Company::query()->find($companyId);
        $complaintsPublicUrl = $company?->complaints_public_token
            ? url('/denuncia/'.$company->complaints_public_token)
            : null;

        return Inertia::render('Client/Dashboard', [
            'activeSurveys' => $activeSurveys,
            'lastSurvey' => $lastSurvey,
            'overallRisk' => $overall,
            'complaintsPublicUrl' => $complaintsPublicUrl,
        ]);
    }
}
