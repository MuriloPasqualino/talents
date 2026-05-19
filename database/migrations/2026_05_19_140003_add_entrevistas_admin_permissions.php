<?php

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
        $userIds = DB::table('users')
            ->where('role', 'super_admin')
            ->where(function ($q) {
                $q->where('is_owner', false)->orWhereNull('is_owner');
            })
            ->pluck('id');

        foreach ($userIds as $userId) {
            foreach ($this->actionValues() as $action) {
                $exists = DB::table('admin_user_permissions')
                    ->where('user_id', $userId)
                    ->where('module', 'entrevistas')
                    ->where('action', $action)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('admin_user_permissions')->insert([
                    'user_id' => $userId,
                    'module' => 'entrevistas',
                    'action' => $action,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('admin_user_permissions')->where('module', 'entrevistas')->delete();
    }
};
