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
            'resolved' => false,
            'created_at' => now(),
        ]);

        foreach ([
            '/',
            '/dashboard',
            '/logs',
            '/logs/' . $logId,
            '/archived-logs',
            '/archived-logs/' . $archivedLog->id,
            '/error-codes',
            '/error-codes/' . $errorCode->id,
        ] as $uri) {
            $this->get($uri)->assertRedirect('http://auth.example.com/login');
        }

        $this->get('/sse/logs')->assertUnauthorized();

        $this->delete('/archived-logs/' . $archivedLog->id)
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
        $this->get('/archived-logs')->assertOk()->assertViewIs('archived-logs.index');
        $this->get('/archived-logs/' . $archivedLog->id)->assertOk()->assertViewIs('logs.show');
        $this->get('/error-codes')->assertOk()->assertViewIs('error-codes.index');
        $this->get('/error-codes/' . $errorCode->id)->assertOk()->assertViewIs('error-codes.show');

        $this->get('/sse/logs')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/event-stream; charset=UTF-8');
    }

    public function test_back_navigation_chain_preserves_logs_origin_without_back_query_param(): void
    {
        [$user, , $archivedLog, $logId] = $this->seedPanelRecords();
        $this->actingAs($user);

        $logsListUrl = url('/logs?search=seed&resolved=unresolved');
        $logDetailUrl = url('/logs/' . $logId);
        $archivedDetailUrl = url('/archived-logs/' . $archivedLog->id);

        $this->withHeader('referer', $logsListUrl)
            ->get('/logs/' . $logId)
            ->assertOk()
            ->assertViewHas('backHref', $logsListUrl)
            ->assertDontSee('back=');

        $this->withHeader('referer', $logDetailUrl)
            ->get('/archived-logs/' . $archivedLog->id)
            ->assertOk()
            ->assertViewHas('backHref', $logDetailUrl)
            ->assertDontSee('back=');

        $this->withHeader('referer', $archivedDetailUrl)
            ->get('/logs/' . $logId)
            ->assertOk()
            ->assertViewHas('backHref', $logsListUrl)
            ->assertDontSee('back=');
    }

    public function test_archived_log_delete_route_soft_deletes_and_hides_from_default_views(): void
    {
        [$owner, , $archivedLog] = $this->seedPanelRecords();

        $intruder = User::factory()->create();

        $this->actingAs($intruder)
            ->delete('/archived-logs/' . $archivedLog->id)
            ->assertForbidden();

        $this->actingAs($owner)
            ->delete('/archived-logs/' . $archivedLog->id)
            ->assertRedirect(route('archived-logs.index'));

        $this->assertSoftDeleted('archived_logs', [
            'id' => $archivedLog->id,
        ]);

        $this->get('/archived-logs/' . $archivedLog->id)
            ->assertNotFound();
    }

    public function test_two_year_old_archived_log_and_comments_remain_accessible_when_not_deleted(): void
    {
        [$user, , $archivedLog] = $this->seedPanelRecords();

        $archivedLog->update([
            'archived_at' => now()->subYears(2),
            'original_created_at' => now()->subYears(2)->subDay(),
        ]);

        Comment::query()->create([
            'commentable_type' => ArchivedLog::class,
            'commentable_id' => $archivedLog->id,
            'user_id' => $user->id,
            'content' => 'Comentario A historico',
        ]);

        Comment::query()->create([
            'commentable_type' => ArchivedLog::class,
            'commentable_id' => $archivedLog->id,
            'user_id' => $user->id,
            'content' => 'Comentario B historico',
        ]);

        $this->actingAs($user)
            ->get('/archived-logs/' . $archivedLog->id)
            ->assertOk()
            ->assertSee('Archived test log');

        Livewire::test(CommentThread::class, [
            'commentableType' => 'archived-log',
            'commentableId' => $archivedLog->id,
        ])
            ->assertSee('Comentario A historico')
            ->assertSee('Comentario B historico');
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

        Livewire::test(CommentThread::class, [
            'commentableType' => 'archived-log',
            'commentableId' => $archivedLog->id,
        ])
            ->call('deleteComment', $comment->id)
            ->assertForbidden();
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
            'resolved' => false,
            'created_at' => now(),
        ]);

        return [$user, $errorCode, $archivedLog, $logId];
    }
}