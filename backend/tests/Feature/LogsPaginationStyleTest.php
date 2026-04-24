<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogsPaginationStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_logs_pagination_renders_compact_pages_and_showing_text_on_first_page(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'Panel App',
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'E-1000',
            'application_id' => $application->id,
            'name' => 'Primary error',
            'description' => 'Test description',
            'severity' => 'critical',
        ]);

        $this->seedLogs(count: 330, applicationId: $application->id, errorCodeId: $errorCode->id);

        $this->actingAs($user);

        $response = $this->get('/logs');

        $response
            ->assertOk()
            ->assertSee('Mostrando 1-25 de 330')
            ->assertSee('Anterior')
            ->assertSee('Siguiente')
            // Solo aparece en la zona de paginación (en los mensajes NO insertamos "...")
            ->assertSee('...')
            ->assertSee('Go to page 13')
            ->assertSee('Go to page 14');
    }

    public function test_logs_pagination_updates_showing_text_on_second_page(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'Panel App',
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'E-1000',
            'application_id' => $application->id,
            'name' => 'Primary error',
            'description' => 'Test description',
            'severity' => 'critical',
        ]);

        $this->seedLogs(count: 330, applicationId: $application->id, errorCodeId: $errorCode->id);

        $this->actingAs($user);

        $response = $this->get('/logs?page=2');

        $response
            ->assertOk()
            ->assertSee('Mostrando 26-50 de 330')
            ->assertSee('Anterior')
            ->assertSee('Siguiente')
            ->assertSee('...')
            ->assertSee('Go to page 1')
            ->assertSee('Go to page 3')
            ->assertSee('Go to page 13');
    }

    private function seedLogs(int $count, int $applicationId, int $errorCodeId): void
    {
        $start = now()->subMinutes($count);

        // Nota: no usamos el modelo `Log` porque deshabilita CRUD (hook `booted()`),
        // así que insertamos directo para generar datos de paginación.
        $batch = [];

        for ($i = 0; $i < $count; $i++) {
            $batch[] = [
                'error_code_id' => $errorCodeId,
                'application_id' => $applicationId,
                'severity' => 'critical',
                'message' => sprintf('Seed log #%03d', $i),
                'file' => 'app/Jobs/Test.php',
                'line' => 12,
                'metadata' => json_encode(['context' => 'test', 'i' => $i], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'resolved' => false,
                'created_at' => $start->copy()->addMinutes($i),
            ];

            if (count($batch) >= 100) {
                DB::table('logs')->insert($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            DB::table('logs')->insert($batch);
        }
    }
}
