<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('task_cards', function (Blueprint $table) {
            $table->string('recurrence', 16)->nullable()->after('due_date');
            $table->date('recurrence_ends_on')->nullable()->after('recurrence');
        });
    }

    public function down(): void
    {
        Schema::table('task_cards', function (Blueprint $table) {
            $table->dropColumn(['recurrence', 'recurrence_ends_on']);
        });
    }
};
