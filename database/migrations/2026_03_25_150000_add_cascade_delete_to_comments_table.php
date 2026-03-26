<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Cascade delete for morphMany comments is handled programmatically
     * in the ErrorCode and ArchivedLog models via deleting event listeners.
     * 
     * This migration is a placeholder to mark that cascade delete was reviewed.
     * The actual cascade is implemented in the model's boot() method to ensure
     * it works reliably across all database engines.
     */
    public function up(): void
    {
        // See ErrorCode::boot() and ArchivedLog::boot() for cascade delete logic
    }

    public function down(): void
    {
        // No database changes to revert
    }
};
