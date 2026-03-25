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

class LogsSeverityFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_by_single_severity_via_ui_other(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'other' => ['other log A'],
            'medium' => ['medium log A'],
        ]);

        Livewire::test(LogsTable::class)
            ->set('severityInput', ['other'])
            ->call('applyFilters')
            ->assertSee('other log A')
            ->assertDontSee('critical log A')
            ->assertDontSee('high log A')
            ->assertDontSee('medium log A');
    }

    public function test_filter_by_single_severity_via_ui_critical(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'medium' => ['medium log A'],
        ]);

        Livewire::test(LogsTable::class)
            ->set('severityInput', ['critical'])
            ->call('applyFilters')
            ->assertSee('critical log A')
            ->assertDontSee('high log A')
            ->assertDontSee('medium log A');
    }

    public function test_filter_by_single_severity_critical(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'medium' => ['medium log A'],
        ]);

        $this->get('/logs?severity=critical')
            ->assertOk()
            ->assertSee('critical log A')
            ->assertDontSee('high log A')
            ->assertDontSee('medium log A');
    }

    public function test_filter_by_single_severity_via_url_other(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'other' => ['other log A'],
            'medium' => ['medium log A'],
        ]);

        $this->get('/logs?severity=other')
            ->assertOk()
            ->assertSee('other log A')
            ->assertDontSee('critical log A')
            ->assertDontSee('high log A')
            ->assertDontSee('medium log A');
    }

    public function test_filter_multi_severity_critical_and_high(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'medium' => ['medium log A'],
        ]);

        Livewire::test(LogsTable::class)
            ->set('severityInput', ['critical', 'high'])
            ->call('applyFilters')
            ->assertSee('critical log A')
            ->assertSee('high log A')
            ->assertDontSee('medium log A');
    }

    public function test_clear_severity_filter_returns_all(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'medium' => ['medium log A'],
        ]);

        Livewire::test(LogsTable::class)
            ->set('severityInput', ['critical', 'high'])
            ->call('applyFilters')
            ->assertSee('critical log A')
            ->assertSee('high log A')
            ->assertDontSee('medium log A')
            ->call('resetFilters')
            ->assertSee('critical log A')
            ->assertSee('high log A')
            ->assertSee('medium log A');
    }

    public function test_pre_filtered_from_dashboard_high_is_active_in_component(): void
    {
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $this->seedLogs($application->id, $errorCode->id, [
            'critical' => ['critical log A'],
            'high' => ['high log A'],
            'medium' => ['medium log A'],
        ]);

        Livewire::test(LogsTable::class, [
            // Simulates DashboardController: route('logs.index', ['severity' => 'high'])
            'severity' => 'high',
        ])
            ->assertSee('high log A')
            ->assertDontSee('critical log A')
            ->assertDontSee('medium log A');
    }

    public function test_invalid_severity_outside_enum_returns_422(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->seedApplicationAndErrorCode();

        $this->get('/logs?severity=not-a-real-severity')
            ->assertStatus(422);
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
            'code' => 'E-1',
            'application_id' => $application->id,
            'name' => 'Primary error',
            'description' => 'Test description',
            'severity' => 'high',
        ]);

        return [$application, $errorCode];
    }

    /**
     * @param  array<string,array<int,string>>  $messagesBySeverity
     */
    private function seedLogs(int $applicationId, int $errorCodeId, array $messagesBySeverity): void
    {
        $rows = [];
        $now = now();

        $i = 0;
        foreach ($messagesBySeverity as $severity => $messages) {
            foreach ($messages as $message) {
                $rows[] = [
                    'error_code_id' => $errorCodeId,
                    'application_id' => $applicationId,
                    'severity' => $severity,
                    'message' => $message,
                    'file' => 'app/Jobs/Test.php',
                    'line' => 12,
                    'metadata' => json_encode(['context' => 'test'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'resolved' => false,
                    'created_at' => $now->copy()->subSeconds($i++),
                ];
            }
        }

        DB::table('logs')->insert($rows);
    }
}

