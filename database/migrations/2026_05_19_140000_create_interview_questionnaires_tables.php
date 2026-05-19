<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('interview_questionnaire_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('interview_questionnaires')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('interview_questionnaire_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained('interview_questionnaire_sections')->cascadeOnDelete();
            $table->string('question_key');
            $table->text('text');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['section_id', 'question_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_questionnaire_questions');
        Schema::dropIfExists('interview_questionnaire_sections');
        Schema::dropIfExists('interview_questionnaires');
    }
};
