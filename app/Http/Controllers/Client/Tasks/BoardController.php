<?php

namespace App\Http\Controllers\Client\Tasks;

use App\Http\Controllers\Controller;
use App\Models\TaskBoard;
use App\Models\TaskCard;
use App\Support\Tasks\BoardPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = (int) $user->company_id;

        $boards = TaskBoard::query()
            ->where('is_archived', false)
            ->where(function ($q) use ($companyId) {
                $q->whereNull('company_id')
                    ->orWhere('company_id', $companyId);
            })
            ->with(['company:id,name'])
            ->withCount([
                'lists' => fn ($q) => $q->where('is_archived', false),
            ])
            ->orderByRaw('company_id is null desc')
            ->orderBy('name')
            ->get()
            ->filter(fn (TaskBoard $board) => $user->can('view', $board))
            ->values()
            ->map(function (TaskBoard $board) use ($companyId) {
                $cardsCount = TaskCard::query()
                    ->whereHas('list', fn ($q) => $q->where('board_id', $board->id)->where('is_archived', false))
                    ->where('is_archived', false)
                    ->visibleToCompany($companyId)
                    ->count();

                return [
                    'id' => $board->id,
                    'name' => $board->name,
                    'description' => $board->description,
                    'cover_color' => $board->cover_color,
                    'is_internal' => $board->company_id === null,
                    'company' => $board->company ? ['id' => $board->company->id, 'name' => $board->company->name] : null,
                    'lists_count' => $board->lists_count,
                    'cards_count' => $cardsCount,
                ];
            });

        return Inertia::render('Client/Tarefas/Index', [
            'boards' => $boards,
        ]);
    }

    public function show(Request $request, TaskBoard $board): Response
    {
        $this->authorize('view', $board);

        $payload = BoardPresenter::forClient($board, (int) $request->user()->company_id);
        $companyUsers = BoardPresenter::companyUsersForMentions((int) $request->user()->company_id);

        return Inertia::render('Client/Tarefas/Show', [
            'boardPayload' => $payload,
            'companyUsers' => $companyUsers,
        ]);
    }
}
