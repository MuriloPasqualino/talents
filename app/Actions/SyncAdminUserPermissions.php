<?php

namespace App\Actions;

use App\Enums\AdminPermissionModule;
use App\Enums\PermissionAction;
use App\Models\User;

class SyncAdminUserPermissions
{
    /**
     * @param  array<int, array{module: string, action: string}>  $permissions
     */
    public function execute(User $user, array $permissions): void
    {
        $user->adminPermissions()->delete();

        foreach ($permissions as $row) {
            if (! isset($row['module'], $row['action'])) {
                continue;
            }

            $mod = AdminPermissionModule::from($row['module']);
            $act = PermissionAction::from($row['action']);

            $user->adminPermissions()->create([
                'module' => $mod->value,
                'action' => $act->value,
            ]);
        }
    }
}
