<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    /**
     * Escenario 1: Conexión exitosa a PostgreSQL.
     */
    public function test_can_connect_to_postgresql(): void
    {
        $result = DB::select('SELECT 1 AS connected');

        $this->assertEquals(1, $result[0]->connected);
    }

    /**
     * Escenario 2: El usuario del panel no puede hacer DELETE en archived_logs.
     * Requiere que el usuario 'panel_user' esté creado en la BD.
     */
    #[\PHPUnit\Framework\Attributes\Group('integration')]
    public function test_panel_user_cannot_delete_archived_logs(): void
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        DB::connection('panel')->statement('DELETE FROM archived_logs WHERE id = 0');
    }

    /**
     * Escenario 3: Las credenciales no están en archivos versionados.
     */
    public function test_env_file_is_not_committed(): void
    {
        $this->assertFileDoesNotExist(base_path('.env.backup'));
        $this->assertStringContainsString('.env', file_get_contents(base_path('.gitignore')));
    }
}
