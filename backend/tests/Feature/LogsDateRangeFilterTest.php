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

class LogsDateRangeFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_valid_inclusive_date_range(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->insertLog($application->id, $errorCode->id, 'before range', '2026-03-09 23:59:59');
        $this->insertLog($application->id, $errorCode->id, 'start boundary', '2026-03-10 00:00:00');
        $this->insertLog($application->id, $errorCode->id, 'middle range', '2026-03-12 10:00:00');
        $this->insertLog($application->id, $errorCode->id, 'end boundary', '2026-03-15 23:59:59');
        $this->insertLog($application->id, $errorCode->id, 'after range', '2026-03-16 00:00:00');

        $from = rawurlencode('2026-03-10T00:00:00Z');
        $to = rawurlencode('2026-03-15T23:59:59Z');

        $this->get("/logs?date_from={$from}&date_to={$to}")
            ->assertOk()
            ->assertSee('start boundary')
            ->assertSee('middle range')
            ->assertSee('end boundary')
            ->assertDontSee('before range')
            ->assertDontSee('after range');
    }

    public function test_filter_with_only_start_date_includes_today(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->insertLog($application->id, $errorCode->id, 'old log', now()->subDays(10)->toDateTimeString());
        $this->insertLog($application->id, $errorCode->id, 'recent log', now()->subDay()->toDateTimeString());

        $from = rawurlencode(now()->subDays(2)->utc()->toIso8601String());

        $this->get("/logs?date_from={$from}")
            ->assertOk()
            ->assertSee('recent log')
            ->assertDontSee('old log');
    }

    public function test_invalid_date_range_shows_validation_error(): void
    {
        Livewire::test(LogsTable::class)
            ->set('dateFromInput', '2026-03-15T00:00:00Z')
            ->set('dateToInput', '2026-03-10T23:59:59Z')
            ->call('applyFilters')
            ->assertHasErrors(['dateToInput' => 'after_or_equal']);
    }

    public function test_accepts_native_datepicker_values_and_normalizes_them(): void
    {
        Livewire::test(LogsTable::class)
            ->set('dateFromInput', '2026-03-10')
            ->set('dateToInput', '2026-03-15')
            ->call('applyFilters')
            ->assertHasNoErrors()
            ->assertSet('dateFrom', '2026-03-10T00:00:00+00:00')
            ->assertSet('dateTo', '2026-03-15T23:59:59+00:00');
    }

    public function test_frontend_contains_utc_normalization_to_iso8601(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/logs')
            ->assertOk()
            ->assertSee('toISOString()', false);
    }

    public function test_reset_filters_dispatches_date_range_reset_event(): void
    {
        Livewire::test(LogsTable::class)
            ->set('dateFromInput', '2026-03-10')
            ->set('dateToInput', '2026-03-15')
            ->call('resetFilters')
            ->assertDispatched('date-range-reset');
    }

    /**
     * @return array{0: Application, 1: ErrorCode}
     */
    private function seedApplicationAndErrorCode(): array
    {
        $application = Application::query()->create([
            'name' => 'api-gateway',
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'API-001',
            'application_id' => $application->id,
            'name' => 'Gateway error',
            'description' => 'Gateway description',
            'severity' => 'high',
        ]);

        return [$application, $errorCode];
    }

    private function insertLog(int $applicationId, int $errorCodeId, string $message, string $createdAt): void
    {
        DB::table('logs')->insert([
            'error_code_id' => $errorCodeId,
            'application_id' => $applicationId,
            'severity' => 'critical',
            'message' => $message,
            'file' => 'app/Jobs/Test.php',
            'line' => 12,
            'metadata' => json_encode(['context' => 'test'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved' => false,
            'created_at' => $createdAt,
        ]);
    }
}
