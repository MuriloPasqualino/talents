<?php

namespace App\Http\Controllers\Client;

use App\Enums\StrategicCalendarItemKind;
use App\Http\Controllers\Controller;
use App\Models\StrategicCalendarItem;
use App\Support\StrategicCalendarOccurrenceExpander;
use App\Support\StrategicCalendarPeriod;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StrategicCalendarController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $user = $request->user();
        $company = $user->company;

        $requestedYear = max(2000, min(2100, (int) $request->input('year', now()->year)));
        $requestedMonth = max(1, min(12, (int) $request->input('month', now()->month)));

        $view = StrategicCalendarPeriod::resolveClientView($company, $requestedYear, $requestedMonth);
        $year = $view['year'];
        $month = $view['month'];

        $monthStart = Carbon::create($year, $month, 1)->startOfDay();
        $monthEnd = $monthStart->copy()->endOfMonth()->endOfDay();

        $range = $view['range'];
        $queryStart = $range ? max($monthStart, $range['start']) : $monthStart;
        $queryEnd = $range ? min($monthEnd, $range['end']) : $monthEnd;

        $monthMasters = StrategicCalendarOccurrenceExpander::baseQueryForRange(
            StrategicCalendarItem::query()->forCompany($company),
            $queryStart,
            $queryEnd,
        )->orderBy('occurs_on')->orderBy('id')->get();

        $monthItems = StrategicCalendarOccurrenceExpander::expandCollection(
            $monthMasters,
            $queryStart,
            $queryEnd,
            'client.strategic-calendar.attachment',
        );

        $upcomingStart = now()->startOfDay();
        $upcomingEnd = $range
            ? $range['end']->copy()->endOfDay()
            : now()->copy()->addYears(2)->endOfDay();

        $upcomingMasters = StrategicCalendarOccurrenceExpander::baseQueryForRange(
            StrategicCalendarItem::query()->forCompany($company),
            $upcomingStart,
            $upcomingEnd,
        )->orderBy('occurs_on')->orderBy('id')->get();

        $upcomingExpanded = StrategicCalendarOccurrenceExpander::expandCollection(
            $upcomingMasters,
            $upcomingStart,
            $upcomingEnd,
            'client.strategic-calendar.attachment',
        );

        $upcoming = $upcomingExpanded
            ->filter(fn (array $row) => $row['occurs_on'] >= $upcomingStart->toDateString())
            ->take(12)
            ->values();

        $agendaStart = now()->toDateString();
        $agendaEndCarbon = now()->copy()->addDays(60)->endOfDay();
        if ($range) {
            $agendaEndCarbon = $agendaEndCarbon->min($range['end']);
        }

        $agendaMasters = StrategicCalendarOccurrenceExpander::baseQueryForRange(
            StrategicCalendarItem::query()->forCompany($company),
            Carbon::parse($agendaStart)->startOfDay(),
            $agendaEndCarbon,
        )->orderBy('occurs_on')->orderBy('id')->get();

        $agendaItems = StrategicCalendarOccurrenceExpander::expandCollection(
            $agendaMasters,
            Carbon::parse($agendaStart)->startOfDay(),
            $agendaEndCarbon,
            'client.strategic-calendar.attachment',
        );

        return Inertia::render('Client/StrategicCalendar/Index', [
            'monthItems' => $monthItems,
            'upcoming' => $upcoming,
            'agendaItems' => $agendaItems,
            'calendarYear' => $year,
            'calendarMonth' => $month,
            'visiblePeriod' => $view['visiblePeriod'],
            'canNavigatePrev' => $view['canNavigatePrev'],
            'canNavigateNext' => $view['canNavigateNext'],
            'kindLabels' => collect(StrategicCalendarItemKind::cases())->mapWithKeys(
                fn (StrategicCalendarItemKind $k) => [$k->value => $k->label()]
            ),
        ]);
    }

    public function attachment(Request $request, StrategicCalendarItem $item): StreamedResponse|Response
    {
        $company = $request->user()->company;

        if ($item->company_id !== null && (int) $item->company_id !== (int) $company->id) {
            abort(404);
        }

        if (! $item->hasAttachment()) {
            abort(404);
        }

        return Storage::disk($item->attachment_disk)->download(
            $item->attachment_path,
            $item->attachment_original_name ?? 'anexo',
        );
    }
}
