<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropForeign(['survey_template_question_id']);
            $table->foreign('survey_template_question_id')
                ->references('id')
                ->on('survey_template_questions')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('survey_answers', function (Blueprint $table) {
            $table->dropForeign(['survey_template_question_id']);
            $table->foreign('survey_template_question_id')
                ->references('id')
                ->on('survey_template_questions')
                ->cascadeOnDelete();
        });
    }
};
