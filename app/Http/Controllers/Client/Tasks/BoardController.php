<?php

namespace App\Http\Controllers\Client\Tasks;

use App\Http\Controllers\Controller;
use App\Models\TaskBoard;
use App\Support\Tasks\BoardPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BoardController extends Controller
{
    public function index(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        $board = TaskBoard::query()
            ->whereNull('company_id')
            ->where('is_archived', false)
            ->orderBy('id')
            ->first();

        if ($board) {
            return redirect()->route('client.tarefas.show', $board);
        }

        return Inertia::render('Client/Tarefas/Index', ['boards' => []]);
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
