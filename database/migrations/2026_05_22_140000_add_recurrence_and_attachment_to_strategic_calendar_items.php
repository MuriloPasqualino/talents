<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('strategic_calendar_items', function (Blueprint $table) {
            $table->string('recurrence', 16)->nullable()->after('occurs_on');
            $table->date('recurrence_ends_on')->nullable()->after('recurrence');
            $table->string('attachment_disk', 32)->nullable()->after('company_id');
            $table->string('attachment_path')->nullable()->after('attachment_disk');
            $table->string('attachment_original_name')->nullable()->after('attachment_path');
            $table->string('attachment_mime', 128)->nullable()->after('attachment_original_name');
            $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
        });
    }

    public function down(): void
    {
        Schema::table('strategic_calendar_items', function (Blueprint $table) {
            $table->dropColumn([
                'recurrence',
                'recurrence_ends_on',
                'attachment_disk',
                'attachment_path',
                'attachment_original_name',
                'attachment_mime',
                'attachment_size',
            ]);
        });
    }
};
