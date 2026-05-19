<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')
            ->where('key', 'metodologia')
            ->update([
                'name' => 'Direcionamento Estratégico',
                'description' => 'Jornada de diagnóstico, pesquisa de satisfação e etapas do direcionamento estratégico.',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('modules')
            ->where('key', 'metodologia')
            ->update([
                'name' => 'Metodologia Talents',
                'description' => 'Jornada de diagnóstico, pesquisa de satisfação e etapas da metodologia.',
                'updated_at' => now(),
            ]);
    }
};
