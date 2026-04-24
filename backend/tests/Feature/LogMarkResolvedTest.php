<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LogMarkResolvedTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolve_route_marks_log_resolved_and_redirects_to_detail(): void
    {
        [$user, $application, $errorCode] = $this->seedBaseData();

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'critical',
            'message' => 'Log to resolve',
            'file' => 'app/Test.php',
            'line' => 1,
            'metadata' => json_encode([]),
            'resolved' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->patch(route('logs.resolve', $logId))
            ->assertRedirect(route('logs.show', $logId));

        $this->assertDatabaseHas('logs', [
            'id' => $logId,
            'resolved' => true,
        ]);
    }

    /**
     * @return array{0: User, 1: Application, 2: ErrorCode}
     */
    private function seedBaseData(): array
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'Resolve Test App',
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'RES-001',
            'application_id' => $application->id,
            'name' => 'Resolve test error',
            'description' => null,
            'severity' => 'high',
        ]);

        return [$user, $application, $errorCode];
    }
}
