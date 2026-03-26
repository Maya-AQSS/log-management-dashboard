<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogDetailAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_path_clicking_log_opens_detail_with_full_metadata_fields(): void
    {
        [$user, $application, $errorCode] = $this->seedBaseData();

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'critical',
            'message' => 'A complete message for detail screen',
            'file' => 'app/Services/LogIngestor.php',
            'line' => 87,
            'metadata' => json_encode([
                'request_id' => 'req-123',
                'stack_trace' => "Trace line 1\nTrace line 2",
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/logs')
            ->assertOk()
            ->assertSee(route('logs.show', $logId), false);

        $this->actingAs($user)
            ->get('/logs/'.$logId)
            ->assertOk()
            ->assertSee(__('logs.detail.id'))
            ->assertSee((string) $logId)
            ->assertSee(__('logs.table.application'))
            ->assertSee($application->name)
            ->assertSee(__('logs.table.severity'))
            ->assertSee('CRITICAL')
            ->assertSee(__('logs.table.message'))
            ->assertSee('A complete message for detail screen')
            ->assertSee(__('logs.table.created_at'))
            ->assertSee(__('logs.detail.file'))
            ->assertSee('app/Services/LogIngestor.php')
            ->assertSee(__('logs.detail.line'))
            ->assertSee('87')
            ->assertSee(__('logs.detail.metadata'))
            ->assertSee('request_id')
            ->assertSee('stack_trace');
    }

    public function test_long_message_and_stack_trace_are_scrollable_and_not_truncated_in_detail(): void
    {
        [$user, $application, $errorCode] = $this->seedBaseData();

        $longMessage = str_repeat('Long message segment. ', 400).'END_OF_LONG_MESSAGE';
        $longStackTrace = str_repeat('Stack trace line -> /app/Service.php:42\n', 200).'END_OF_STACK_TRACE';

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'high',
            'message' => $longMessage,
            'file' => 'app/Services/Foo.php',
            'line' => 42,
            'metadata' => json_encode([
                'stack_trace' => $longStackTrace,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/logs/'.$logId)
            ->assertOk()
            ->assertSee('END_OF_LONG_MESSAGE')
            ->assertSee('END_OF_STACK_TRACE')
            ->assertSee('max-h-64 overflow-y-auto', false);
    }

    public function test_back_button_preserves_active_filters_from_logs_table(): void
    {
        [$user, $application, $errorCode] = $this->seedBaseData();

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'medium',
            'message' => 'Navigation check log',
            'file' => 'app/Http/Controllers/Foo.php',
            'line' => 21,
            'metadata' => json_encode(['context' => 'navigation'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved' => false,
            'created_at' => now(),
        ]);

        $filtersUrl = url('/logs?search=navigation&severity=medium&resolved=unresolved&date_from=2026-03-10T00%3A00%3A00Z');

        $this->actingAs($user)
            ->withHeader('referer', $filtersUrl)
            ->get('/logs/'.$logId)
            ->assertOk()
            ->assertViewHas('backHref', $filtersUrl)
            ->assertSee('href="'.e($filtersUrl).'"', false);
    }

    public function test_url_tutorial_is_visible_only_for_archived_detail_not_active_logs_detail(): void
    {
        [$user, $application, $errorCode] = $this->seedBaseData();

        $tutorialUrl = 'https://docs.example.com/tutorial/fix-database-timeout';

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived with tutorial',
            'metadata' => ['origin' => 'tests'],
            'description' => null,
            'url_tutorial' => $tutorialUrl,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        $activeLogId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'high',
            'message' => 'Active log without tutorial field',
            'file' => 'app/Services/Bar.php',
            'line' => 52,
            'metadata' => json_encode(['context' => 'active'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/logs/'.$activeLogId)
            ->assertOk()
            ->assertDontSee(__('archived_logs.url_tutorial.section_title'))
            ->assertDontSee($tutorialUrl);

        $this->actingAs($user)
            ->get('/archived-logs/'.$archivedLog->id)
            ->assertOk()
            ->assertSee(__('archived_logs.url_tutorial.section_title'))
            ->assertSee('href="'.$tutorialUrl.'"', false);
    }

    /**
     * @return array{0: User, 1: Application, 2: ErrorCode}
     */
    private function seedBaseData(): array
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'Acceptance App',
            'description' => 'Acceptance tests app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'ACC-001',
            'application_id' => $application->id,
            'name' => 'Acceptance error',
            'description' => 'Acceptance description',
            'severity' => 'high',
        ]);

        return [$user, $application, $errorCode];
    }
}
