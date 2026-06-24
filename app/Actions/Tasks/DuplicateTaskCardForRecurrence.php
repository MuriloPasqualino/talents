<?php

namespace App\Actions\Tasks;

use App\Enums\TaskCardRecurrence;
use App\Models\TaskCard;
use App\Models\TaskList;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DuplicateTaskCardForRecurrence
{
    public function __construct(
        private LogTaskActivity $logTaskActivity,
    ) {}

    public function handle(TaskCard $source, TaskList $targetList, string $nextDueDate, User $actor): TaskCard
    {
        return DB::transaction(function () use ($source, $targetList, $nextDueDate, $actor) {
            $source->loadMissing(['labels', 'members', 'list.board']);
            $board = $targetList->board ?? $source->list?->board;

            $max = (float) $targetList->cards()->max('position');

            $card = TaskCard::query()->create([
                'list_id' => $targetList->id,
                'company_id' => $source->company_id,
                'title' => $source->title,
                'description' => $source->description,
                'position' => $max + 1000,
                'visibility' => $source->visibility,
                'cover_color' => $source->cover_color,
                'start_date' => null,
                'due_date' => $nextDueDate,
                'recurrence' => $source->recurrence,
                'recurrence_ends_on' => $source->recurrence_ends_on,
                'completed_at' => null,
                'is_archived' => false,
                'created_by_user_id' => $actor->id,
            ]);

            $card->labels()->sync($source->labels()->pluck('task_labels.id')->all());
            $card->members()->sync($source->members()->pluck('users.id')->all());

            if ($board) {
                $this->logTaskActivity->handle($board, $card, 'card.recurrence_spawned', $actor, [
                    'source_card_id' => $source->id,
                    'next_due_date' => $nextDueDate,
                ]);
            }

            return $card->fresh(['list', 'labels', 'members']);
        });
    }
}
