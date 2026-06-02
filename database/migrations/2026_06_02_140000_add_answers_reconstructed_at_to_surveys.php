<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->timestamp('answers_reconstructed_at')->nullable()->after('min_responses_for_breakdown');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('answers_reconstructed_at');
        });
    }
};
