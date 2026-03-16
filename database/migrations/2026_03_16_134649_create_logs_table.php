<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla gestionada externamente por n8n.
     * Esta migración la crea solo si no existe (entornos de desarrollo/test).
     *
     * Estructura esperada en producción:
     *   id               bigserial PK
     *   error_code_id    bigint FK nullable → error_codes.id
     *   application_id   bigint FK → applications.id
     *   severity         enum (critical, high, medium, low, other)
     *   message          text
     *   file             varchar nullable
     *   line             integer nullable
     *   metadata         jsonb nullable
     *   matched_archived_log_id  bigint nullable
     *   resolved         boolean default false
     *   created_at       timestamptz
     */
    public function up(): void
    {
        if (Schema::hasTable('logs')) {
            return;
        }

        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('error_code_id')->nullable()->constrained('error_codes')->nullOnDelete();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'other']);
            $table->text('message');
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->unsignedBigInteger('matched_archived_log_id')->nullable();
            $table->boolean('resolved')->default(false);
            $table->timestampTz('created_at')->nullable();

            $table->index('error_code_id');
            $table->index(['application_id', 'created_at']);
            $table->index(['severity', 'resolved']);
            $table->index('matched_archived_log_id');
        });
    }

    public function down(): void
    {
        // Precaución: en producción esta tabla es gestionada por n8n.
        Schema::dropIfExists('logs');
    }
};
