<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commercial_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();

            // Lead (cliente potencial)
            $table->string('client_name');
            $table->string('client_cnpj', 18)->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone', 32)->nullable();
            $table->string('indication')->nullable();
            $table->unsignedInteger('employee_count')->default(0);

            // Vendedor responsável
            $table->foreignId('seller_id')->nullable()->constrained('users')->nullOnDelete();

            // Serviços contratados
            $table->boolean('svc_pesquisas')->default(false);
            $table->boolean('svc_profiler')->default(false);
            $table->string('svc_devolutiva', 16)->nullable(); // null | individual | grupo
            $table->boolean('svc_nr1')->default(false);
            $table->string('svc_nr1_implantacao_modo', 16)->nullable(); // null | online | presencial
            $table->boolean('svc_contratacao')->default(false);
            $table->unsignedInteger('svc_contratacao_salario_cents')->nullable();
            $table->boolean('svc_direcionamento')->default(false);
            $table->boolean('svc_palestras')->default(false);

            // Snapshot dos cálculos (centavos) — preserva o que foi cobrado mesmo se preços mudarem
            $table->unsignedInteger('total_pesquisas_cents')->default(0);
            $table->unsignedInteger('total_profiler_cents')->default(0);
            $table->unsignedInteger('total_devolutiva_cents')->default(0);
            $table->unsignedInteger('total_nr1_cents')->default(0);
            $table->unsignedInteger('total_nr1_implantacao_cents')->default(0);
            $table->unsignedInteger('total_contratacao_cents')->default(0);
            $table->unsignedInteger('total_direcionamento_cents')->default(0);
            $table->unsignedInteger('total_palestras_cents')->default(0);
            $table->unsignedInteger('total_final_cents')->default(0);

            // Comissão
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->unsignedInteger('commission_cents')->default(0);

            // Status comercial
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_closed', 'created_at']);
            $table->index('seller_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commercial_proposals');
    }
};
