<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->longText('technical_opinion')->nullable()->after('admin_published_at');
        });
    }

    public function down(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->dropColumn('technical_opinion');
        });
    }
};
