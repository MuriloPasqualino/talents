<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_template_questions', function (Blueprint $table) {
            $table->decimal('weight', 5, 2)->default(1.00)->after('reverse_score');
        });
    }

    public function down(): void
    {
        Schema::table('survey_template_questions', function (Blueprint $table) {
            $table->dropColumn('weight');
        });
    }
};
