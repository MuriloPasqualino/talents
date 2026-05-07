<?php

namespace App\Http\Controllers\Admin\Commercial;

use App\Http\Controllers\Controller;
use App\Models\CommercialProposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalCount = CommercialProposal::count();
        $closedCount = CommercialProposal::where('is_closed', true)->count();
        $totalBudgetCents = (int) CommercialProposal::sum('total_final_cents');
        $totalClosedCents = (int) CommercialProposal::where('is_closed', true)->sum('total_final_cents');
        $avgTicketCents = $totalCount > 0 ? (int) round($totalBudgetCents / $totalCount) : 0;

        return Inertia::render('Admin/Comercial/Dashboard', [
            'kpis' => [
                'total_count' => $totalCount,
                'closed_count' => $closedCount,
                'total_budget_cents' => $totalBudgetCents,
                'total_closed_cents' => $totalClosedCents,
                'avg_ticket_cents' => $avgTicketCents,
            ],
            'byService' => $this->summaryByService(),
            'bySeller' => $this->summaryBySeller(),
            'recent' => CommercialProposal::query()
                ->with('seller:id,name')
                ->latest()
                ->limit(10)
                ->get(['id', 'code', 'client_name', 'seller_id', 'employee_count', 'total_final_cents', 'is_closed', 'created_at']),
        ]);
    }

    /**
     * Espelha a aba "Resumo" da planilha: por serviço, contagem e total
     * tanto no orçado quanto no fechado.
     *
     * @return array<int, array<string, mixed>>
     */
    private function summaryByService(): array
    {
        $services = [
            ['key' => 'svc_pesquisas', 'label' => 'Pesquisas e Organograma', 'totalCol' => 'total_pesquisas_cents'],
            ['key' => 'svc_profiler', 'label' => 'Profiler', 'totalCol' => 'total_profiler_cents'],
            ['key' => 'svc_devolutiva', 'label' => 'Devolutiva', 'totalCol' => 'total_devolutiva_cents'],
            ['key' => 'svc_nr1', 'label' => 'NR-1 Mapeamento', 'totalCol' => 'total_nr1_cents'],
            ['key' => 'svc_nr1_implantacao_modo', 'label' => 'NR-1 Implantação', 'totalCol' => 'total_nr1_implantacao_cents'],
            ['key' => 'svc_contratacao', 'label' => 'Contratação', 'totalCol' => 'total_contratacao_cents'],
            ['key' => 'svc_direcionamento', 'label' => 'Direcionamento Estratégico', 'totalCol' => 'total_direcionamento_cents'],
            ['key' => 'svc_palestras', 'label' => 'Palestras e Treinamentos', 'totalCol' => 'total_palestras_cents'],
        ];

        $rows = [];
        foreach ($services as $svc) {
            $isStringCol = in_array($svc['key'], ['svc_devolutiva', 'svc_nr1_implantacao_modo'], true);

            $base = CommercialProposal::query()->when(
                $isStringCol,
                fn ($q) => $q->whereNotNull($svc['key']),
                fn ($q) => $q->where($svc['key'], true),
            );

            $rows[] = [
                'label' => $svc['label'],
                'budget_count' => (clone $base)->count(),
                'budget_total_cents' => (int) (clone $base)->sum($svc['totalCol']),
                'closed_count' => (clone $base)->where('is_closed', true)->count(),
                'closed_total_cents' => (int) (clone $base)->where('is_closed', true)->sum($svc['totalCol']),
            ];
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function summaryBySeller(): array
    {
        $sellers = User::query()
            ->where('is_commercial', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $sellers->map(function (User $seller) {
            $base = CommercialProposal::query()->where('seller_id', $seller->id);

            return [
                'seller_id' => $seller->id,
                'name' => $seller->name,
                'budget_count' => (clone $base)->count(),
                'budget_total_cents' => (int) (clone $base)->sum('total_final_cents'),
                'closed_count' => (clone $base)->where('is_closed', true)->count(),
                'closed_total_cents' => (int) (clone $base)->where('is_closed', true)->sum('total_final_cents'),
                'commission_total_cents' => (int) (clone $base)->where('is_closed', true)->sum('commission_cents'),
            ];
        })->all();
    }
}
