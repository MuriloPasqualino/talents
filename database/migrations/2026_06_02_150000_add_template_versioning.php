<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_templates', function (Blueprint $table) {
            $table->foreignId('forked_from_id')
                ->nullable()
                ->after('created_by')
                ->constrained('survey_templates')
                ->nullOnDelete();
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['survey_template_id']);
            $table->foreign('survey_template_id')
                ->references('id')
                ->on('survey_templates')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropForeign(['survey_template_id']);
            $table->foreign('survey_template_id')
                ->references('id')
                ->on('survey_templates')
                ->cascadeOnDelete();
        });

        Schema::table('survey_templates', function (Blueprint $table) {
            $table->dropForeign(['forked_from_id']);
            $table->dropColumn('forked_from_id');
        });
    }
};
