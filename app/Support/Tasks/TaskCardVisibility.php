<?php

namespace App\Support\Tasks;

use App\Enums\TaskCardVisibility as TaskCardVisibilityEnum;
use App\Enums\TaskListVisibility;
use App\Models\TaskCard;
use App\Models\TaskList;

final class TaskCardVisibility
{
    public static function isVisibleToCompany(TaskCard $card, ?TaskList $list = null): bool
    {
        $list ??= $card->list;
        if (! $list) {
            return false;
        }

        $cardAllowsPortal = match ($card->visibility) {
            TaskCardVisibilityEnum::Internal->value => false,
            TaskCardVisibilityEnum::Company->value,
            TaskCardVisibilityEnum::Inherit->value => true,
            default => false,
        };

        if (! $cardAllowsPortal) {
            return false;
        }

        if ($list->visibility === TaskListVisibility::Company->value) {
            return true;
        }

        if ($list->visibility === TaskListVisibility::Internal->value) {
            return $card->company_id !== null;
        }

        return false;
    }

    public static function companyMayMoveBetween(TaskList $from, TaskList $to): bool
    {
        if ($to->visibility !== TaskListVisibility::Company->value) {
            return false;
        }

        if (! (bool) $to->allow_company_drop_in) {
            return false;
        }

        if ($from->visibility === TaskListVisibility::Company->value) {
            return true;
        }

        return $from->visibility === TaskListVisibility::Internal->value;
    }
}
