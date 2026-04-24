<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Models\User;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivedLogsSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_severity_sort_asc_orders_critical_before_low(): void
    {
        $user = User::factory()->create();
        $application = Application::query()->create([
            'name' => 'Archived Sort App',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'AS-1',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        $archivedAt = '2026-01-15 12:00:00';

        ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'low',
            'message' => 'msg-low',
            'metadata' => null,
            'description' => null,
            'original_created_at' => now()->subDay(),
            'archived_at' => $archivedAt,
        ]);
        ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'critical',
            'message' => 'msg-critical',
            'metadata' => null,
            'description' => null,
            'original_created_at' => now()->subDay(),
            'archived_at' => $archivedAt,
        ]);

        $paginator = app(ArchivedLogRepositoryInterface::class)->searchAndFilter(
            null,
            null,
            null,
            null,
            'severity',
            'asc',
            15
        );

        $items = $paginator->items();
        $this->assertSame('critical', $items[0]->severity);
        $this->assertSame('low', $items[1]->severity);
    }

    public function test_severity_sort_desc_orders_low_before_critical(): void
    {
        $user = User::factory()->create();
        $application = Application::query()->create([
            'name' => 'Archived Sort App 2',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'AS-2',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        $archivedAt = '2026-01-15 12:00:00';

        ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'critical',
            'message' => 'msg-critical',
            'metadata' => null,
            'description' => null,
            'original_created_at' => now()->subDay(),
            'archived_at' => $archivedAt,
        ]);
        ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'low',
            'message' => 'msg-low',
            'metadata' => null,
            'description' => null,
            'original_created_at' => now()->subDay(),
            'archived_at' => $archivedAt,
        ]);

        $paginator = app(ArchivedLogRepositoryInterface::class)->searchAndFilter(
            null,
            null,
            null,
            null,
            'severity',
            'desc',
            15
        );

        $items = $paginator->items();
        $this->assertSame('low', $items[0]->severity);
        $this->assertSame('critical', $items[1]->severity);
    }
}
