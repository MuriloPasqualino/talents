<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32)->default('openai');
            $table->text('api_key')->nullable();
            $table->string('model', 128)->default('gpt-4o-mini');
            $table->boolean('is_enabled')->default(false);
            $table->unsignedInteger('max_tokens')->default(2000);
            $table->decimal('temperature', 3, 2)->default(0.30);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');
    }
};
