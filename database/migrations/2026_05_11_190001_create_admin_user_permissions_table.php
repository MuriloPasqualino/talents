<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return list<string>
     */
    private function adminModuleValues(): array
    {
        return [
            'dashboard',
            'landing_interest',
            'companies',
            'plans',
            'survey_templates',
            'methodology',
            'strategic_calendar',
            'tarefas',
            'comercial',
            'empresa_talents',
            'solides',
            'settings',
            'training',
            'equipa',
        ];
    }

    /**
     * @return list<string>
     */
    private function actionValues(): array
    {
        return ['view', 'create', 'edit', 'delete'];
    }

    public function up(): void
    {
        Schema::create('admin_user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('module');
            $table->string('action');
            $table->timestamps();

            $table->unique(['user_id', 'module', 'action']);
        });

        $now = now();
        $userIds = DB::table('users')
            ->where('role', 'super_admin')
            ->where(function ($q) {
                $q->where('is_owner', false)->orWhereNull('is_owner');
            })
            ->pluck('id');

        foreach ($userIds as $userId) {
            foreach ($this->adminModuleValues() as $module) {
                foreach ($this->actionValues() as $action) {
                    DB::table('admin_user_permissions')->insert([
                        'user_id' => $userId,
                        'module' => $module,
                        'action' => $action,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_user_permissions');
    }
};
