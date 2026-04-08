<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StrategicCalendarItemKind;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\StrategicCalendarItem;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StrategicCalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $year = max(2000, min(2100, (int) $request->input('year', now()->year)));
        $month = max(1, min(12, (int) $request->input('month', now()->month)));

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        $listQuery = StrategicCalendarItem::query()
            ->with('company:id,name')
            ->orderByDesc('occurs_on')
            ->orderByDesc('id');

        if ($request->filled('company_id')) {
            $cid = (int) $request->input('company_id');
            $listQuery->where(function ($q) use ($cid) {
                $q->whereNull('company_id')->orWhere('company_id', $cid);
            });
        }

        $items = $listQuery->paginate(20)->withQueryString();

        $monthQuery = StrategicCalendarItem::query()
            ->with('company:id,name')
            ->whereBetween('occurs_on', [$monthStart->toDateString(), $monthEnd->toDateString()]);

        if ($request->filled('company_id')) {
            $cid = (int) $request->input('company_id');
            $monthQuery->where(function ($q) use ($cid) {
                $q->whereNull('company_id')->orWhere('company_id', $cid);
            });
        }

        $monthItems = $monthQuery->orderBy('occurs_on')->orderBy('id')->get();

        return Inertia::render('Admin/StrategicCalendar/Index', [
            'items' => $items,
            'monthItems' => $monthItems,
            'calendarYear' => $year,
            'calendarMonth' => $month,
            'filters' => $request->only(['company_id']),
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'kindLabels' => collect(StrategicCalendarItemKind::cases())->mapWithKeys(
                fn (StrategicCalendarItemKind $k) => [$k->value => $k->label()]
            ),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/StrategicCalendar/Create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'kinds' => collect(StrategicCalendarItemKind::cases())->map(fn (StrategicCalendarItemKind $k) => [
                'value' => $k->value,
                'label' => $k->label(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        StrategicCalendarItem::query()->create($data);

        return redirect()->route('admin.strategic-calendar.index')->with('success', 'Item do calendário criado.');
    }

    public function edit(StrategicCalendarItem $item): Response
    {
        return Inertia::render('Admin/StrategicCalendar/Edit', [
            'item' => $item,
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'kinds' => collect(StrategicCalendarItemKind::cases())->map(fn (StrategicCalendarItemKind $k) => [
                'value' => $k->value,
                'label' => $k->label(),
            ]),
        ]);
    }

    public function update(Request $request, StrategicCalendarItem $item): RedirectResponse
    {
        $data = $this->validated($request);

        $item->update($data);

        return redirect()->route('admin.strategic-calendar.index')->with('success', 'Item atualizado.');
    }

    public function destroy(StrategicCalendarItem $item): RedirectResponse
    {
        $item->delete();

        return redirect()->route('admin.strategic-calendar.index')->with('success', 'Item removido.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'kind' => ['required', 'string', Rule::enum(StrategicCalendarItemKind::class)],
            'occurs_on' => ['required', 'date'],
            'company_id' => ['nullable', 'exists:companies,id'],
        ]);

        $data['company_id'] = $data['company_id'] ?? null;

        return $data;
    }
}
