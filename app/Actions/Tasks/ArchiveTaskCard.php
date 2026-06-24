<?php

namespace App\Actions\Tasks;

use App\Models\TaskCard;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ArchiveTaskCard
{
    public function __construct(
        private LogTaskActivity $logTaskActivity,
    ) {}

    public function handle(TaskCard $card, User $actor): TaskCard
    {
        return DB::transaction(function () use ($card, $actor) {
            $card->loadMissing('list.board');
            $board = $card->list?->board;

            $card->update(['is_archived' => true]);

            if ($board) {
                $this->logTaskActivity->handle($board, $card->fresh(), 'card.archived', $actor, []);
            }

            return $card->fresh(['list']);
        });
    }
}
