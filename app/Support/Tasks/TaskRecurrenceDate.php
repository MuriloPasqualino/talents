<?php

namespace App\Support\Tasks;

use App\Enums\TaskCardRecurrence;
use Carbon\Carbon;

final class TaskRecurrenceDate
{
    public static function nextAfter(Carbon $current, TaskCardRecurrence $recurrence): Carbon
    {
        return match ($recurrence) {
            TaskCardRecurrence::Daily => $current->copy()->addDay(),
            TaskCardRecurrence::Weekly => $current->copy()->addWeek(),
            TaskCardRecurrence::Monthly => $current->copy()->addMonthNoOverflow(),
            TaskCardRecurrence::Annual => $current->copy()->addYear(),
        };
    }

    public static function shouldSpawnNext(?Carbon $nextDue, ?Carbon $recurrenceEndsOn): bool
    {
        if ($recurrenceEndsOn === null) {
            return true;
        }

        return $nextDue !== null && $nextDue->lte($recurrenceEndsOn);
    }
}
