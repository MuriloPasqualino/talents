<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_settings', function (Blueprint $table) {
            $table->string('company_contract_signatory_name', 255)->nullable()->after('company_forum_city_state');
            $table->string('company_contract_signatory_cpf', 32)->nullable()->after('company_contract_signatory_name');
        });

        Schema::table('commercial_proposals', function (Blueprint $table) {
            $table->string('client_representative_role', 255)->nullable()->after('client_representative');
            $table->string('palestra_topic', 500)->nullable()->after('client_representative_role');
            $table->date('palestra_event_date')->nullable()->after('palestra_topic');
            $table->string('palestra_start_time', 32)->nullable()->after('palestra_event_date');
            $table->string('palestra_duration_hours', 32)->nullable()->after('palestra_start_time');
            $table->string('palestra_venue_address', 500)->nullable()->after('palestra_duration_hours');
            $table->unsignedInteger('palestra_audience_estimate')->nullable()->after('palestra_venue_address');
            $table->string('palestra_format', 16)->nullable()->after('palestra_audience_estimate');
        });
    }

    public function down(): void
    {
        Schema::table('commercial_settings', function (Blueprint $table) {
            $table->dropColumn(['company_contract_signatory_name', 'company_contract_signatory_cpf']);
        });

        Schema::table('commercial_proposals', function (Blueprint $table) {
            $table->dropColumn([
                'client_representative_role',
                'palestra_topic',
                'palestra_event_date',
                'palestra_start_time',
                'palestra_duration_hours',
                'palestra_venue_address',
                'palestra_audience_estimate',
                'palestra_format',
            ]);
        });
    }
};
