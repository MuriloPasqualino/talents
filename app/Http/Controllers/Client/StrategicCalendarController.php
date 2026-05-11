<?php

namespace App\Http\Controllers\Client;

use App\Enums\StrategicCalendarItemKind;
use App\Http\Controllers\Controller;
use App\Models\StrategicCalendarItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StrategicCalendarController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $company = $user->company;

        $year = max(2000, min(2100, (int) $request->input('year', now()->year)));
        $month = max(1, min(12, (int) $request->input('month', now()->month)));

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        $monthItems = StrategicCalendarItem::query()
            ->forCompany($company)
            ->whereBetween('occurs_on', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('occurs_on')
            ->orderBy('id')
            ->get();

        $upcoming = StrategicCalendarItem::query()
            ->forCompany($company)
            ->whereDate('occurs_on', '>=', now()->toDateString())
            ->orderBy('occurs_on')
            ->orderBy('id')
            ->limit(12)
            ->get();

        $agendaEnd = now()->copy()->addDays(60)->endOfDay();
        $agendaItems = StrategicCalendarItem::query()
            ->forCompany($company)
            ->whereDate('occurs_on', '>=', now()->toDateString())
            ->whereDate('occurs_on', '<=', $agendaEnd->toDateString())
            ->orderBy('occurs_on')
            ->orderBy('id')
            ->get();

        return Inertia::render('Client/StrategicCalendar/Index', [
            'monthItems' => $monthItems,
            'upcoming' => $upcoming,
            'agendaItems' => $agendaItems,
            'calendarYear' => $year,
            'calendarMonth' => $month,
            'kindLabels' => collect(StrategicCalendarItemKind::cases())->mapWithKeys(
                fn (StrategicCalendarItemKind $k) => [$k->value => $k->label()]
            ),
        ]);
    }
}
