<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade la restricción UNIQUE a users.external_id.
 *
 * PostgreSQL permite múltiples NULLs bajo una columna UNIQUE,
 * por lo que los usuarios sin external_id (NULL) no se ven afectados.
 *
 * Resuelve el TODO en la migración original de users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
        });
    }
};
