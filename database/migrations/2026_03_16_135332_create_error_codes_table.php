<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->foreignId('application_id')->constrained('applications')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('severity', ['critical', 'high', 'medium', 'low', 'other'])->nullable();
            $table->unsignedBigInteger('default_role_id')->nullable();
            $table->timestamps();

            $table->unique(['code', 'application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_codes');
    }
};
