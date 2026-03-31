<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DashboardApplicationTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_application_totals_and_links_to_filtered_logs(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $appA = Application::query()->create([
            'name' => 'Alpha Service',
            'description' => 'A',
            'created_at' => now(),
        ]);
        $appB = Application::query()->create([
            'name' => 'Beta Service',
            'description' => 'B',
            'created_at' => now(),
        ]);
        $appEmpty = Application::query()->create([
            'name' => 'No Logs App',
            'description' => 'C',
            'created_at' => now(),
        ]);

        $ecA = ErrorCode::query()->create([
            'code' => 'E-A',
            'application_id' => $appA->id,
            'name' => 'Err A',
            'description' => 'd',
            'severity' => 'high',
        ]);
        $ecB = ErrorCode::query()->create([
            'code' => 'E-B',
            'application_id' => $appB->id,
            'name' => 'Err B',
            'description' => 'd',
            'severity' => 'medium',
        ]);

        $now = now();
        DB::table('logs')->insert([
            [
                'error_code_id' => $ecA->id,
                'application_id' => $appA->id,
                'severity' => 'high',
                'message' => 'log one',
                'file' => 'a.php',
                'line' => 1,
                'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
                'resolved' => false,
                'created_at' => $now,
            ],
            [
                'error_code_id' => $ecA->id,
                'application_id' => $appA->id,
                'severity' => 'high',
                'message' => 'log two',
                'file' => 'a.php',
                'line' => 2,
                'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
                'resolved' => false,
                'created_at' => $now,
            ],
            [
                'error_code_id' => $ecB->id,
                'application_id' => $appB->id,
                'severity' => 'medium',
                'message' => 'log three',
                'file' => 'b.php',
                'line' => 1,
                'metadata' => json_encode([], JSON_UNESCAPED_UNICODE),
                'resolved' => false,
                'created_at' => $now,
            ],
        ]);

        $response = $this->get('/dashboard');
        $response->assertOk();
        $response->assertSee('Alpha Service', false);
        $response->assertSee('Beta Service', false);
        $response->assertDontSee('No Logs App', false);

        $hrefA = route('logs.index', ['application' => $appA->id]);
        $hrefB = route('logs.index', ['application' => $appB->id]);
        $response->assertSee($hrefA, false);
        $response->assertSee($hrefB, false);

        $content = $response->getContent();
        $this->assertMatchesRegularExpression('/Alpha Service[\s\S]*?2/', $content);
        $this->assertMatchesRegularExpression('/Beta Service[\s\S]*?1/', $content);

        $this->get($hrefA)->assertOk()->assertSee('log one');
    }
}
