<?php

namespace App\Actions\Tasks;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class ArchiveTaskList
{
    public function __construct(
        private LogTaskActivity $logTaskActivity,
    ) {}

    public function handle(TaskList $list, User $actor): TaskList
    {
        return DB::transaction(function () use ($list, $actor) {
            $list->loadMissing('board');
            $board = $list->board;

            $list->cards()->where('is_archived', false)->update(['is_archived' => true]);
            $list->update(['is_archived' => true]);

            if ($board) {
                $this->logTaskActivity->handle($board, null, 'list.archived', $actor, ['list_id' => $list->id]);
            }

            return $list->fresh();
        });
    }
}
