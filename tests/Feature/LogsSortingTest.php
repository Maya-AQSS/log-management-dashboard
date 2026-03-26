<?php

namespace Tests\Feature;

use App\Livewire\LogsTable;
use App\Models\Application;
use App\Models\ErrorCode;
use App\Repositories\Contracts\LogRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class LogsSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_date_header_first_click_sorts_oldest_first_and_second_click_descending(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode('Sorting App');

        $this->insertLog($application->id, $errorCode->id, 'date oldest', '2026-03-10 10:00:00', 'critical');
        $this->insertLog($application->id, $errorCode->id, 'date middle', '2026-03-11 10:00:00', 'critical');
        $this->insertLog($application->id, $errorCode->id, 'date newest', '2026-03-12 10:00:00', 'critical');

        Livewire::test(LogsTable::class)
            ->call('sortByColumn', 'created_at')
            ->assertSet('sortBy', 'created_at')
            ->assertSet('sortDir', 'asc')
            ->assertSeeInOrder(['date oldest', 'date middle', 'date newest'])
            ->call('sortByColumn', 'created_at')
            ->assertSet('sortDir', 'desc')
            ->assertSeeInOrder(['date newest', 'date middle', 'date oldest']);
    }

    public function test_active_sorted_column_shows_direction_indicator(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode('Indicator App');

        $this->insertLog($application->id, $errorCode->id, 'indicator row', '2026-03-12 10:00:00', 'critical');

        Livewire::test(LogsTable::class)
            ->call('sortByColumn', 'created_at')
            ->assertSee('↑')
            ->assertDontSee('↓')
            ->call('sortByColumn', 'created_at')
            ->assertSee('↓');
    }

    public function test_sorting_keeps_applied_filters_intact(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode('Filter App');

        $this->insertLog($application->id, $errorCode->id, 'critical old', '2026-03-10 10:00:00', 'critical');
        $this->insertLog($application->id, $errorCode->id, 'high only', '2026-03-11 10:00:00', 'high');
        $this->insertLog($application->id, $errorCode->id, 'critical new', '2026-03-12 10:00:00', 'critical');

        Livewire::test(LogsTable::class)
            ->set('severityInput', ['critical'])
            ->call('applyFilters')
            ->assertSee('critical old')
            ->assertSee('critical new')
            ->assertDontSee('high only')
            ->call('sortByColumn', 'created_at')
            ->assertSeeInOrder(['critical old', 'critical new'])
            ->assertDontSee('high only');
    }

    public function test_sorting_by_application_uses_application_name(): void
    {
        [$appZulu, $errorCodeZulu] = $this->seedApplicationAndErrorCode('Zulu App', 'ZULU-1');
        [$appAlpha, $errorCodeAlpha] = $this->seedApplicationAndErrorCode('Alpha App', 'ALPHA-1');

        $this->insertLog($appZulu->id, $errorCodeZulu->id, 'zulu log', '2026-03-10 10:00:00', 'critical');
        $this->insertLog($appAlpha->id, $errorCodeAlpha->id, 'alpha log', '2026-03-10 11:00:00', 'critical');

        Livewire::test(LogsTable::class)
            ->call('sortByColumn', 'application')
            ->assertSet('sortBy', 'application')
            ->assertSet('sortDir', 'asc')
            ->assertSeeInOrder(['alpha log', 'zulu log']);
    }

    public function test_backend_whitelist_rejects_invalid_sort_by_with_safe_fallback(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode('Whitelist App');

        $this->insertLog($application->id, $errorCode->id, 'fallback oldest', '2026-03-10 10:00:00', 'critical');
        $this->insertLog($application->id, $errorCode->id, 'fallback newest', '2026-03-12 10:00:00', 'critical');

        $paginator = app(LogRepositoryInterface::class)->searchAndFilter(
            search: null,
            severity: null,
            applicationId: null,
            archived: null,
            resolved: null,
            dateFrom: null,
            dateTo: null,
            sortBy: 'created_at; DROP TABLE logs;--',
            sortDir: 'desc',
            perPage: 25
        );

        $messages = array_map(static fn ($log) => $log->message, $paginator->items());

        $this->assertSame(['fallback newest', 'fallback oldest'], $messages);
    }

    public function test_backend_whitelist_rejects_invalid_sort_dir_and_defaults_to_asc(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode('Sort Dir App');

        $this->insertLog($application->id, $errorCode->id, 'dir oldest', '2026-03-10 10:00:00', 'critical');
        $this->insertLog($application->id, $errorCode->id, 'dir newest', '2026-03-12 10:00:00', 'critical');

        $paginator = app(LogRepositoryInterface::class)->searchAndFilter(
            search: null,
            severity: null,
            applicationId: null,
            archived: null,
            resolved: null,
            dateFrom: null,
            dateTo: null,
            sortBy: 'created_at',
            sortDir: 'invalid-direction',
            perPage: 25
        );

        $messages = array_map(static fn ($log) => $log->message, $paginator->items());

        $this->assertSame(['dir oldest', 'dir newest'], $messages);
    }

    /**
     * @return array{0: Application, 1: ErrorCode}
     */
    private function seedApplicationAndErrorCode(string $applicationName, string $errorCode = 'E-SORT'): array
    {
        $application = Application::query()->create([
            'name' => $applicationName,
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCodeModel = ErrorCode::query()->create([
            'code' => $errorCode,
            'application_id' => $application->id,
            'name' => 'Sorting error',
            'description' => 'Sorting description',
            'severity' => 'high',
        ]);

        return [$application, $errorCodeModel];
    }

    private function insertLog(int $applicationId, int $errorCodeId, string $message, string $createdAt, string $severity): void
    {
        DB::table('logs')->insert([
            'error_code_id' => $errorCodeId,
            'application_id' => $applicationId,
            'severity' => $severity,
            'message' => $message,
            'file' => 'app/Jobs/Test.php',
            'line' => 12,
            'metadata' => json_encode(['context' => 'test'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved' => false,
            'created_at' => $createdAt,
        ]);
    }
}
