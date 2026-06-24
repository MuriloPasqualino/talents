<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_settings', function (Blueprint $table) {
            $table->unsignedInteger('direcionamento_hora_cents')
                ->nullable()
                ->after('direcionamento_tier4_cents');
        });

        Schema::table('commercial_proposals', function (Blueprint $table) {
            $table->decimal('direcionamento_horas', 8, 2)
                ->nullable()
                ->after('svc_direcionamento');
        });

        DB::table('commercial_settings')->update([
            'direcionamento_hora_cents' => DB::raw('COALESCE(direcionamento_tier1_cents, 5000)'),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('commercial_proposals', function (Blueprint $table) {
            $table->dropColumn('direcionamento_horas');
        });

        Schema::table('commercial_settings', function (Blueprint $table) {
            $table->dropColumn('direcionamento_hora_cents');
        });
    }
};
