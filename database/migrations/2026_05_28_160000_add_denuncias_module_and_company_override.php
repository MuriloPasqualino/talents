<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $denunciasId = DB::table('modules')->where('key', 'denuncias')->value('id');
        if (! $denunciasId) {
            $denunciasId = DB::table('modules')->insertGetId([
                'key' => 'denuncias',
                'name' => 'Canal de denúncias',
                'description' => 'Canal de denúncias anônimas e gestão de protocolos (Lei nº 14.457/2022).',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $nr1Id = DB::table('modules')->where('key', 'nr1')->value('id');
        if ($nr1Id) {
            $planIds = DB::table('module_plan')->where('module_id', $nr1Id)->pluck('plan_id')->unique();

            foreach ($planIds as $planId) {
                DB::table('module_plan')->updateOrInsert(
                    ['plan_id' => $planId, 'module_id' => $denunciasId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }

        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('denuncias_access')->nullable()->after('rhid_access');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('denuncias_access');
        });

        $id = DB::table('modules')->where('key', 'denuncias')->value('id');
        if (! $id) {
            return;
        }

        DB::table('module_plan')->where('module_id', $id)->delete();
        DB::table('modules')->where('id', $id)->delete();
    }
};
