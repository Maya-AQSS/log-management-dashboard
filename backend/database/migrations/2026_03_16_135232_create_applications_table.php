<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Aplicaciones: FDW → maya_auth.applications (fuente de verdad del ecosistema).
 *
 * El servidor FDW (maya_auth_server) y el user mapping se crean en init-databases.sh
 * como superuser (maya), porque log_mgmt_user no es superuser.
 *
 * La vista 'applications' expone tanto 'name' (nombre legible) como 'slug' (técnico).
 * ConsumeLogs identifica apps por slug; el frontend puede mostrar el name real.
 */
return new class extends Migration
{
    private const SERVER  = 'maya_auth_server';
    private const FDW_TBL = 'applications_fdw';
    private const VIEW    = 'applications';

    public function up(): void
    {
        $dbUser = config('database.connections.pgsql.username', 'log_mgmt_user');

        // Idempotente: drop primero para que migrate:fresh no falle
        DB::statement('DROP VIEW IF EXISTS ' . self::VIEW . ' CASCADE');
        DB::statement('DROP FOREIGN TABLE IF EXISTS ' . self::FDW_TBL . ' CASCADE');

        DB::statement("
            CREATE FOREIGN TABLE " . self::FDW_TBL . " (
                id          bigint       NOT NULL,
                name        varchar(255) NOT NULL,
                slug        varchar(100) NOT NULL,
                description text,
                is_active   boolean      NOT NULL DEFAULT true,
                created_at  timestamp
            )
            SERVER " . self::SERVER . "
            OPTIONS (schema_name 'public', table_name 'applications')
        ");

        DB::statement("
            CREATE VIEW " . self::VIEW . " AS
            SELECT id, name, slug, description, is_active, created_at
            FROM " . self::FDW_TBL . "
        ");

        DB::statement("GRANT SELECT ON " . self::FDW_TBL . " TO \"{$dbUser}\"");
        DB::statement("GRANT SELECT ON " . self::VIEW . " TO \"{$dbUser}\"");
    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS ' . self::VIEW . ' CASCADE');
        DB::statement('DROP FOREIGN TABLE IF EXISTS ' . self::FDW_TBL . ' CASCADE');
    }
};
