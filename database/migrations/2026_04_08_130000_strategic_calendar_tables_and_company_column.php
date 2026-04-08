<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->boolean('strategic_calendar_access')->nullable()->after('is_active');
        });

        Schema::create('strategic_calendar_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('kind', 16);
            $table->date('occurs_on');
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['occurs_on']);
            $table->index(['company_id', 'occurs_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategic_calendar_items');

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('strategic_calendar_access');
        });
    }
};
