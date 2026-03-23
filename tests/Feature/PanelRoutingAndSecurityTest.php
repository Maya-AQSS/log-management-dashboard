<?php

namespace Tests\Feature;

use App\Livewire\CommentThread;
use App\Models\Application;
use App\Models\ArchivedLog;
use App\Models\Comment;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class PanelRoutingAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_panel_pages_redirect_guests_to_login(): void
    {
        $application = Application::query()->create([
            'name' => 'Panel App',
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'E-100',
            'application_id' => $application->id,
            'name' => 'Primary error',
            'description' => 'Test description',
            'severity' => 'high',
        ]);

        $user = User::factory()->create();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived test log',
            'metadata' => ['source' => 'test'],
            'description' => 'Archived details',
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'critical',
            'message' => 'Live test log',
            'file' => 'app/Jobs/Test.php',
            'line' => 12,
            'metadata' => json_encode(['context' => 'test']),
            'matched_archived_log_id' => $archivedLog->id,
            'resolved' => false,
            'created_at' => now(),
        ]);

        foreach ([
            '/',
            '/dashboard',
            '/logs',
            '/logs/' . $logId,
            '/historico',
            '/historico/' . $archivedLog->id,
            '/error-codes',
            '/error-codes/' . $errorCode->id,
        ] as $uri) {
            $this->get($uri)->assertRedirect('http://auth.example.com/login');
        }

        $this->get('/sse/logs')->assertUnauthorized();

        $this->delete('/historico/' . $archivedLog->id)
            ->assertRedirect('http://auth.example.com/login');
    }

    public function test_authenticated_user_can_access_minimum_panel_routes(): void
    {
        [$user, $errorCode, $archivedLog, $logId] = $this->seedPanelRecords();

        $this->actingAs($user);

        $this->get('/')->assertOk()->assertViewIs('dashboard');
        $this->get('/dashboard')->assertOk()->assertViewIs('dashboard');
        $this->get('/logs')->assertOk()->assertViewIs('logs.index');
        $this->get('/logs/' . $logId)->assertOk()->assertViewIs('logs.show');
        $this->get('/historico')->assertOk()->assertViewIs('archived-logs.index');
        $this->get('/historico/' . $archivedLog->id)->assertOk()->assertViewIs('archived-logs.show');
        $this->get('/error-codes')->assertOk()->assertViewIs('error-codes.index');
        $this->get('/error-codes/' . $errorCode->id)->assertOk()->assertViewIs('error-codes.show');

        $this->get('/sse/logs')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
    }

    public function test_archived_log_delete_route_uses_policy_authorization(): void
    {
        [$owner, , $archivedLog] = $this->seedPanelRecords();

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->delete('/historico/' . $archivedLog->id)
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete('/historico/' . $archivedLog->id)
            ->assertRedirect(route('archived-logs.index'));

        $this->assertDatabaseMissing('archived_logs', [
            'id' => $archivedLog->id,
        ]);
    }

    public function test_livewire_comment_actions_validate_user_input_before_persisting(): void
    {
        [$user, , $archivedLog] = $this->seedPanelRecords();

        $this->actingAs($user);

        Livewire::test(CommentThread::class, [
            'commentableType' => 'archived-log',
            'commentableId' => $archivedLog->id,
        ])
            ->set('content', 'ok')
            ->call('addComment')
            ->assertHasErrors(['content' => 'min']);

        $this->assertDatabaseCount('comments', 0);

        Livewire::test(CommentThread::class, [
            'commentableType' => 'archived-log',
            'commentableId' => $archivedLog->id,
        ])
            ->set('content', 'Comentario válido para el histórico')
            ->call('addComment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('comments', [
            'commentable_type' => ArchivedLog::class,
            'commentable_id' => $archivedLog->id,
            'user_id' => $user->id,
            'content' => 'Comentario válido para el histórico',
        ]);
    }

    public function test_livewire_comment_delete_requires_comment_policy(): void
    {
        [$user, , $archivedLog] = $this->seedPanelRecords();

        $otherUser = User::factory()->create();

        $comment = Comment::query()->create([
            'commentable_type' => ArchivedLog::class,
            'commentable_id' => $archivedLog->id,
            'user_id' => $otherUser->id,
            'content' => 'Comentario ajeno',
        ]);

        $this->actingAs($user);

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        Livewire::test(CommentThread::class, [
            'commentableType' => 'archived-log',
            'commentableId' => $archivedLog->id,
        ])->call('deleteComment', $comment->id);
    }

    private function seedPanelRecords(): array
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'Panel App ' . $user->id,
            'description' => 'Test app',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'code' => 'E-' . $user->id,
            'application_id' => $application->id,
            'name' => 'Primary error',
            'description' => 'Test description',
            'severity' => 'high',
        ]);

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived test log',
            'metadata' => ['source' => 'test'],
            'description' => 'Archived details',
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'critical',
            'message' => 'Live test log',
            'file' => 'app/Jobs/Test.php',
            'line' => 12,
            'metadata' => json_encode(['context' => 'test']),
            'matched_archived_log_id' => $archivedLog->id,
            'resolved' => false,
            'created_at' => now(),
        ]);

        return [$user, $errorCode, $archivedLog, $logId];
    }
}