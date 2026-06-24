<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->string('technical_opinion_file_path')->nullable()->after('technical_opinion');
            $table->string('technical_opinion_file_name')->nullable()->after('technical_opinion_file_path');
        });
    }

    public function down(): void
    {
        Schema::table('action_plans', function (Blueprint $table) {
            $table->dropColumn(['technical_opinion_file_path', 'technical_opinion_file_name']);
        });
    }
};
