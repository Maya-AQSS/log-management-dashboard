<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Por qué NO se usa ON DELETE CASCADE a nivel de BD para commentable_id:
     *
     * La columna commentable_id es parte de una relación polimórfica (morphMany).
     * Esto significa que puede referenciar MÚLTIPLES tablas (error_codes, archived_logs, etc.)
     * según el valor de commentable_type.
     *
     * Las foreign keys de BD apuntan a UNA sola tabla; no existe un mecanismo estándar
     * para hacer ON DELETE CASCADE sobre una FK polimórfica que apunte a varias tablas.
     *
     * Por eso el cascade se gestiona a nivel de aplicación:
     *   - ErrorCode::booted()  → evento deleting  → cascade de comments
     *   - ArchivedLog::booted() → evento forceDeleting → cascade de comments
     * ambos dentro de DB::transaction() en su respectivo Service.
     *
     * Si en el futuro comments fuera monolítica (solo una tabla padre),
     * se podría hacer:
     *   $table->foreign('commentable_id')->references('id')->on('error_codes')->cascadeOnDelete();
     * pero eso rompería la flexibilidad polimórfica.
     */
    public function up(): void
    {
        // No hay cambios de esquema: el cascade se gestiona en la capa de aplicación.
        // Ver ErrorCode::booted() y ArchivedLog::booted().
    }

    public function down(): void
    {
        // No database changes to revert
    }
};
