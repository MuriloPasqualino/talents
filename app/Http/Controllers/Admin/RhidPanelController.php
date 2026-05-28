<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Rhid\RhidAdminPortfolioMetricsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RhidPanelController extends Controller
{
    public function index(): Response
    {
        $companies = Company::query()
            ->where('is_active', true)
            ->whereNotNull('rhid_email')
            ->whereNotNull('rhid_password')
            ->orderBy('name')
            ->get(['id', 'name', 'segment', 'rhid_base_url', 'rhid_email', 'rhid_domain']);

        return Inertia::render('Admin/Rhid/Index', [
            'companies' => $companies,
            'segments' => $companies->pluck('segment')->filter()->unique()->sort()->values(),
        ]);
    }

    public function summary(Request $request, RhidAdminPortfolioMetricsService $metrics): JsonResponse
    {
        $refresh = $request->boolean('refresh');
        $segment = $request->filled('segment') ? $request->string('segment')->toString() : null;

        return response()->json($metrics->portfolioMetrics($request->user(), $refresh, $segment));
    }

    public function companyMetrics(Request $request, Company $company, RhidAdminPortfolioMetricsService $metrics): JsonResponse
    {
        $refresh = $request->boolean('refresh');

        return response()->json($metrics->metricsForCompany($company, $request->user(), $refresh));
    }
}
