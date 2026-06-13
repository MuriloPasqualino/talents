<?php

use App\Support\CommercialProposalPdfDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_proposals', function (Blueprint $table) {
            $table->string('pdf_subtitle')->nullable()->after('notes');
            $table->text('pdf_objetivo')->nullable()->after('pdf_subtitle');
            $table->json('service_descriptions')->nullable()->after('pdf_objetivo');
        });

        Schema::table('commercial_settings', function (Blueprint $table) {
            $table->json('pdf_descricoes_servicos')->nullable()->after('pdf_aceite_texto');
            $table->text('pdf_condicoes_pagamento')->nullable()->after('pdf_descricoes_servicos');
            $table->text('pdf_texto_encerramento')->nullable()->after('pdf_condicoes_pagamento');
        });

        $defaults = CommercialProposalPdfDefaults::defaultServiceDescriptions();

        DB::table('commercial_settings')->orderBy('id')->limit(1)->update([
            'pdf_descricoes_servicos' => json_encode($defaults, JSON_UNESCAPED_UNICODE),
            'pdf_condicoes_pagamento' => CommercialProposalPdfDefaults::defaultPaymentConditions(),
            'pdf_texto_encerramento' => CommercialProposalPdfDefaults::defaultClosingText(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('commercial_proposals', function (Blueprint $table) {
            $table->dropColumn(['pdf_subtitle', 'pdf_objetivo', 'service_descriptions']);
        });

        Schema::table('commercial_settings', function (Blueprint $table) {
            $table->dropColumn(['pdf_descricoes_servicos', 'pdf_condicoes_pagamento', 'pdf_texto_encerramento']);
        });
    }
};
