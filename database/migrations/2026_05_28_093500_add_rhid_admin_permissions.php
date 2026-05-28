<?php

use App\Enums\UserRole;
use App\Enums\WorkspaceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @return list<string>
     */
    private function actionValues(): array
    {
        return ['view', 'create', 'edit', 'delete'];
    }

    public function up(): void
    {
        $now = now();
        $workspaceIds = DB::table('user_workspaces')
            ->where('workspace_type', WorkspaceType::Talents->value)
            ->where('role', UserRole::SuperAdmin->value)
            ->where('is_owner', false)
            ->pluck('id');

        foreach ($workspaceIds as $workspaceId) {
            foreach ($this->actionValues() as $action) {
                $exists = DB::table('admin_user_permissions')
                    ->where('user_workspace_id', $workspaceId)
                    ->where('module', 'rhid')
                    ->where('action', $action)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('admin_user_permissions')->insert([
                    'user_workspace_id' => $workspaceId,
                    'module' => 'rhid',
                    'action' => $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('admin_user_permissions')->where('module', 'rhid')->delete();
    }
};
