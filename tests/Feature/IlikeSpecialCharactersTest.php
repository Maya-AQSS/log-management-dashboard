<?php

namespace Tests\Feature;

use App\Livewire\LogsTable;
use App\Models\Application;
use App\Models\ErrorCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Verifica que la búsqueda con ILIKE trata los caracteres especiales
 * de SQL (%, _, \) como literales y no como metacaracteres.
 *
 * Estos tests son relevantes para PostgreSQL: ILIKE usa % y _ como
 * wildcards igual que LIKE. SQLite los trata distinto en algunos casos.
 * Ejecutar con: php artisan test --configuration=phpunit-pgsql.xml
 */
class IlikeSpecialCharactersTest extends TestCase
{
    use RefreshDatabase;

    public function test_ilike_treats_percent_as_literal_not_match_all_wildcard(): void
    {
        // "%" en ILIKE sin escapar = "cualquier cadena" → devolvería TODOS los logs.
        // Con escape correcto debe devolver solo el que contiene "%" literalmente.
        [$app, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($app->id, $errorCode->id, [
            'CPU usage reached 100% threshold',
            'Normal startup sequence completed',
        ]);

        Livewire::test(LogsTable::class)
            ->set('searchInput', '100%')
            ->call('applyFilters')
            ->assertSee('CPU usage reached 100% threshold')
            ->assertDontSee('Normal startup sequence completed');
    }

    public function test_ilike_treats_underscore_as_literal_not_single_char_wildcard(): void
    {
        // "_" en ILIKE sin escapar = "exactamente un carácter" →
        // "user_service" matchearía también "userXservice", "user service", etc.
        [$app, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($app->id, $errorCode->id, [
            'Timeout in user_service while processing request',
            'Timeout in userXservice while processing request',
        ]);

        Livewire::test(LogsTable::class)
            ->set('searchInput', 'user_service')
            ->call('applyFilters')
            ->assertSee('Timeout in user_service while processing request')
            ->assertDontSee('Timeout in userXservice while processing request');
    }

    public function test_ilike_treats_backslash_as_literal_not_escape_char(): void
    {
        // "\" es el carácter de escape por defecto en algunos drivers.
        // Debe buscarse como literal sin romper la query ni devolver resultados vacíos.
        [$app, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($app->id, $errorCode->id, [
            'Config loaded from C:\app\config.php',
            'Config loaded from remote path',
        ]);

        Livewire::test(LogsTable::class)
            ->set('searchInput', 'C:\app')
            ->call('applyFilters')
            ->assertSee('Config loaded from C:\app\config.php')
            ->assertDontSee('Config loaded from remote path');
    }

    /**
     * @return array{0: Application, 1: ErrorCode}
     */
    private function seedApplicationAndErrorCode(): array
    {
        $application = Application::query()->create([
            'name' => 'ILIKE Test App',
            'description' => 'App for ILIKE special chars test',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'E-ILIKE',
            'application_id' => $application->id,
            'name' => 'ILIKE test error',
            'description' => 'Test description',
        ]);

        return [$application, $errorCode];
    }

    /**
     * @param array<int,string> $messages
     */
    private function seedLogs(int $applicationId, int $errorCodeId, array $messages): void
    {
        $rows = [];
        $now = now();

        foreach ($messages as $index => $message) {
            $rows[] = [
                'error_code_id'  => $errorCodeId,
                'application_id' => $applicationId,
                'severity'       => 'high',
                'message'        => $message,
                'file'           => 'app/Jobs/Test.php',
                'line'           => 12,
                'metadata'       => json_encode(['context' => 'test'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'resolved'       => false,
                'created_at'     => $now->copy()->subSeconds($index),
            ];
        }

        DB::table('logs')->insert($rows);
    }
}
