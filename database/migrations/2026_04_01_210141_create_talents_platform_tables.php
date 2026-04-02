<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('cnpj', 18)->nullable()->index();
            $table->string('segment')->nullable();
            $table->unsignedInteger('employee_count_estimate')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 32)->default('company_user')->after('email');
            $table->foreignId('company_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });

        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedInteger('price_monthly_cents')->default(0);
            $table->unsignedInteger('max_employees')->nullable();
            $table->unsignedInteger('max_surveys_per_year')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('module_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['plan_id', 'module_id']);
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('survey_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('survey_template_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('survey_template_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_template_section_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('reverse_score')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('company_survey_template', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'survey_template_id']);
        });

        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->uuid('public_token')->unique();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->string('status', 32)->default('draft');
            $table->unsignedSmallInteger('min_responses_for_breakdown')->default(5);
            $table->timestamps();
        });

        Schema::create('survey_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->index();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('age_range', 32)->nullable();
            $table->string('tenure_range', 32)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_question_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('value');
            $table->timestamps();
            $table->unique(['survey_response_id', 'survey_template_question_id'], 'survey_answers_unique');
        });

        Schema::create('survey_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_section_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('average_score', 8, 3);
            $table->string('risk_level', 16);
            $table->unsignedInteger('respondent_count')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('survey_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->text('message');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('open');
            $table->timestamps();
        });

        Schema::create('action_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('action_plan_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('responsible_name')->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('action_plan_items');
        Schema::dropIfExists('action_plans');
        Schema::dropIfExists('survey_insights');
        Schema::dropIfExists('survey_results');
        Schema::dropIfExists('survey_answers');
        Schema::dropIfExists('survey_responses');
        Schema::dropIfExists('surveys');
        Schema::dropIfExists('company_survey_template');
        Schema::dropIfExists('survey_template_questions');
        Schema::dropIfExists('survey_template_sections');
        Schema::dropIfExists('survey_templates');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('module_plan');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('modules');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn('role');
        });

        Schema::dropIfExists('companies');
    }
};
