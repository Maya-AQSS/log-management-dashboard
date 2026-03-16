<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->foreignId('archived_by_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('error_code_id')->nullable()->constrained('error_codes')->nullOnDelete();
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'other']);
            $table->text('message');
            $table->jsonb('metadata')->nullable();
            $table->text('description')->nullable();
            $table->string('url_tutorial', 500)->nullable();
            $table->timestampTz('original_created_at');
            $table->timestampTz('archived_at');
            $table->timestampTz('updated_at')->nullable();

            $table->index(['application_id', 'archived_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_logs');
    }
};
