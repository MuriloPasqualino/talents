<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_template_questions', function (Blueprint $table) {
            $table->string('response_scale', 16)->default('frequency')->after('weight');
        });
    }

    public function down(): void
    {
        Schema::table('survey_template_questions', function (Blueprint $table) {
            $table->dropColumn('response_scale');
        });
    }
};
