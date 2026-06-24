<?php

namespace App\Actions\Tasks;

use App\Models\TaskList;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RestoreTaskList
{
    public function __construct(
        private LogTaskActivity $logTaskActivity,
    ) {}

    public function handle(TaskList $list, User $actor): TaskList
    {
        return DB::transaction(function () use ($list, $actor) {
            $list->loadMissing('board');
            $board = $list->board;

            $list->update(['is_archived' => false]);
            $list->cards()->where('is_archived', true)->update(['is_archived' => false]);

            if ($board) {
                $this->logTaskActivity->handle($board, null, 'list.restored', $actor, ['list_id' => $list->id]);
            }

            return $list->fresh();
        });
    }
}
