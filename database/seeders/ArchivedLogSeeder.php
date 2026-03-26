<?php

namespace Database\Seeders;

use App\Models\ArchivedLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArchivedLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $messageTail = str_repeat($lorem, 3);
        $veryLongChunk = str_repeat('STACK_TRACE_CHUNK: SQLSTATE[08006] connection failure in ArchiveWorker ', 120);

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

        ArchivedLog::updateOrCreate(
            ['id' => 4],
            [
                'application_id' => 1,
                'archived_by_id' => 1,
                'error_code_id' => 1,
                'severity' => 'high',
                'message' => 'Seed: archived long message fixture' . "\n\n" . $veryLongChunk,
                'metadata' => [
                    'seed' => true,
                    'source' => 'ArchivedLogSeeder',
                    'stack_trace' => $veryLongChunk,
                ],
                'description' => 'Fixture with very long content to verify scroll behavior',
                'url_tutorial' => 'https://example.com/tutorial/long-scroll',
                'original_created_at' => now()->subDay(),
                'archived_at' => now(),
            ]
        );

        // Fixture para comprobar match log <-> archived_log en detalle activo.
        // Debe coincidir exactamente con el primer log seeded en mock-logs.php.
        ArchivedLog::updateOrCreate(
            ['id' => 3],
            [
                'application_id' => 1,
                'archived_by_id' => 1,
                'error_code_id' => 1,
                'severity' => 'critical',
                'message' => sprintf(
                    'Seed: %s log #%03d - %s',
                    'critical',
                    1,
                    $messageTail
                ),
                'metadata' => ['seed' => true, 'source' => 'ArchivedLogSeeder', 'batch' => 'archived-matching'],
                'description' => 'Archived fixture matching log #1',
                'url_tutorial' => null,
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
