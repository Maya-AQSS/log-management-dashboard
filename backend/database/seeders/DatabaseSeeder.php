<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     * UserSeeder omitido: 'users' es una vista FDW de solo lectura sobre v_app_users (Odoo).
     * ApplicationSeeder omitido: 'applications' es una vista FDW de solo lectura sobre maya_auth.applications.
     *
     * Pre-truncate (Patch 26): los sub-seeders asumen que error_codes.id
     * arranca en 1 (LogSeeder usa range(1, 22); ArchivedLogSeeder/CommentSeeder
     * referencian error_code_id = 1 hardcoded). Postgres NO resetea sequences
     * al hacer rollback de transacciones fallidas, así que ejecuciones previas
     * fallidas pueden dejar el sequence avanzado y provocar FK violations al
     * re-seedear. TRUNCATE ... RESTART IDENTITY CASCADE garantiza un estado
     * limpio y determinista cada vez que el seed se ejecuta (que solo ocurre
     * cuando `DB_SEED_MODE=if-empty` decide que la BD está vacía).
     */
    public function run(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('TRUNCATE TABLE comments, logs, archived_logs, error_codes RESTART IDENTITY CASCADE');
        }

        $this->call([
            ErrorCodeSeeder::class,
            LogSeeder::class,
            ArchivedLogSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
