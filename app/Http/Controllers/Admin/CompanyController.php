<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SurveyTemplate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $q = Company::query()->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function ($query) use ($s) {
                $query->where('name', 'like', '%'.$s.'%')
                    ->orWhere('cnpj', 'like', '%'.$s.'%');
            });
        }

        $companies = $q->paginate(15)->withQueryString();

        return Inertia::render('Admin/Companies/Index', [
            'companies' => $companies,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Companies/Create', [
            'plans' => Plan::query()->where('is_active', true)->get(['id', 'name', 'slug']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'segment' => ['nullable', 'string', 'max:120'],
            'employee_count_estimate' => ['nullable', 'integer', 'min:0'],
            'plan_id' => ['nullable', 'exists:plans,id'],
            'is_active' => ['boolean'],
        ]);

        $company = Company::create([
            'name' => $data['name'],
            'legal_name' => $data['legal_name'] ?? null,
            'cnpj' => $data['cnpj'] ?? null,
            'segment' => $data['segment'] ?? null,
            'employee_count_estimate' => $data['employee_count_estimate'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'complaints_public_token' => (string) Str::uuid(),
        ]);

        if (! empty($data['plan_id'])) {
            Subscription::create([
                'company_id' => $company->id,
                'plan_id' => $data['plan_id'],
                'starts_at' => now(),
                'ends_at' => now()->addYear(),
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.companies.show', $company)->with('success', 'Empresa criada.');
    }

    public function show(Company $company): Response
    {
        $company->load(['subscriptions.plan', 'surveyTemplates', 'users']);

        return Inertia::render('Admin/Companies/Show', [
            'company' => $company,
            'complaintsPublicUrl' => $company->complaints_public_token
                ? url('/denuncia/'.$company->complaints_public_token)
                : null,
            'plans' => Plan::query()->where('is_active', true)->get(),
            'templates' => SurveyTemplate::query()->where('is_active', true)->get(['id', 'title']),
        ]);
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('Admin/Companies/Edit', [
            'company' => $company,
            'plans' => Plan::query()->where('is_active', true)->get(['id', 'name', 'slug']),
        ]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'cnpj' => ['nullable', 'string', 'max:18'],
            'segment' => ['nullable', 'string', 'max:120'],
            'employee_count_estimate' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $company->update($data);

        return redirect()->route('admin.companies.show', $company)->with('success', 'Empresa atualizada.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $company->delete();

        return redirect()->route('admin.companies.index')->with('success', 'Empresa removida.');
    }

    public function attachTemplate(Company $company, SurveyTemplate $template): RedirectResponse
    {
        $company->surveyTemplates()->syncWithoutDetaching([$template->id]);

        return back()->with('success', 'Template vinculado à empresa.');
    }

    public function detachTemplate(Company $company, SurveyTemplate $template): RedirectResponse
    {
        $company->surveyTemplates()->detach($template->id);

        return back()->with('success', 'Template removido.');
    }
}
