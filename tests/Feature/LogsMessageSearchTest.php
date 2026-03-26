<?php

namespace Tests\Feature;

use App\Livewire\LogsTable;
use App\Models\Application;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class LogsMessageSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_logs_by_message_case_insensitive(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'Database error: connection refused by upstream',
            'Timeout while calling external service',
            'CONNECTION REFUSED at socket layer',
        ]);

        Livewire::test(LogsTable::class)
            ->set('searchInput', 'connection refused')
            ->call('applyFilters')
            ->assertSee('connection refused by upstream')
            ->assertSee('CONNECTION REFUSED at socket layer')
            ->assertDontSee('Timeout while calling external service');
    }

    public function test_search_with_no_results_shows_empty_message_without_errors(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'Queue worker timeout reached',
            'Error while rotating logs',
        ]);

        Livewire::test(LogsTable::class)
            ->set('searchInput', 'connection refused')
            ->call('applyFilters')
            ->assertSee(__('logs.empty'))
            ->assertDontSee('Queue worker timeout reached')
            ->assertDontSee('Error while rotating logs');
    }

    public function test_search_input_uses_400ms_debounce_in_frontend(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/logs')
            ->assertOk()
            ->assertSee('wire:model.live.debounce.400ms="searchInput"', false);
    }

    public function test_search_escapes_wildcards_to_avoid_match_all_patterns(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'Normal message without wildcard markers',
            'This one contains 100% cpu',
        ]);

        Livewire::test(LogsTable::class)
            ->set('searchInput', '%')
            ->call('applyFilters')
            ->assertSee('This one contains 100% cpu')
            ->assertDontSee('Normal message without wildcard markers');
    }

    /**
     * @return array{0: Application, 1: ErrorCode}
     */
    private function seedApplicationAndErrorCode(): array
    {
        $application = Application::query()->create([
            'name' => 'Panel App',
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'E-2',
            'application_id' => $application->id,
            'name' => 'Primary error',
            'description' => 'Test description',
            'severity' => 'high',
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
                'error_code_id' => $errorCodeId,
                'application_id' => $applicationId,
                'severity' => 'high',
                'message' => $message,
                'file' => 'app/Jobs/Test.php',
                'line' => 12,
                'metadata' => json_encode(['context' => 'test'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'resolved' => false,
                'created_at' => $now->copy()->subSeconds($index),
            ];
        }

        DB::table('logs')->insert($rows);
    }
}
