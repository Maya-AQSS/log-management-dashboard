<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ArchivedLog;

class ArchivedLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArchivedLog::updateOrCreate(
            ['id' => 1],
            [
                'application_id' => 1,
                'archived_by_id' => 1,
                'error_code_id' => 1,
                'severity' => 'low',
                'message' => 'Seed: archived log de prueba',
                'metadata' => ['seed' => true, 'source' => 'ArchivedLogSeeder'],
                'description' => 'Descripción de prueba del histórico de logs',
                'url_tutorial' => 'https://example.com/tutorial',
                'original_created_at' => now()->subDay(),
                'archived_at' => now(),
            ]
        );

        ArchivedLog::updateOrCreate(
            ['id' => 2],
            [
                'application_id' => 1,
                'archived_by_id' => 2,
                'error_code_id' => 1,
                'severity' => 'medium',
                'message' => 'Seed: archived log de prueba',
                'metadata' => ['seed' => true, 'source' => 'ArchivedLogSeeder'],
                'description' => 'Descripción de prueba del histórico de logs',
                'url_tutorial' => 'https://example.com/tutorial',
                'original_created_at' => now()->subDay(),
                'archived_at' => now(),
            ]
        );

        /* 
        TODO: Necesario mientras exista el seeder porque sino el id autoincremental de la BD se desincroniza con el id de la tabla. 
        Resync de la secuencia PostgreSQL para evitar UniqueConstraintViolation al insertar nuevos ArchivedLogs 
        Al eliminarlo será necesario hacer "sail migrate:fresh --seed"
        */
        if (DB::getDriverName() === 'pgsql') {
            DB::statement(
                "SELECT setval(pg_get_serial_sequence('archived_logs', 'id'), (SELECT COALESCE(MAX(id), 1) FROM archived_logs))"
            );
        }
    }
}
