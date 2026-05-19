<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained('interview_questionnaires')->restrictOnDelete();
            $table->string('candidate_name');
            $table->string('position_title')->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 32)->default('queued');
            $table->string('audio_path')->nullable();
            $table->string('audio_mime')->nullable();
            $table->unsignedBigInteger('audio_size')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->longText('transcript_text')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('interview_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('interview_questionnaire_questions')->cascadeOnDelete();
            $table->text('answer');
            $table->text('raw_quote')->nullable();
            $table->timestamps();

            $table->unique(['interview_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_answers');
        Schema::dropIfExists('interviews');
    }
};
