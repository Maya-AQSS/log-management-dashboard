<?php

namespace Tests\Feature;

use App\Livewire\ArchivedLogDetail;
use App\Models\Application;
use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class ArchivedLogDescriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_description_when_empty(): void
    {
        $user = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived message',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        $text = 'Contexto y pasos de resolución resumidos.';

        Livewire::actingAs($user)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->call('enableEdit')
            ->assertSet('isEditing', true)
            ->set('descriptionInput', $text)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('isEditing', false);

        $this->assertSame($text, ArchivedLog::query()->find($archivedLog->id)->description);
    }

    public function test_owner_can_update_existing_description(): void
    {
        $user = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived message',
            'metadata' => null,
            'description' => 'Versión original',
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        $updated = 'Texto actualizado con causa raíz.';

        Livewire::actingAs($user)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->call('enableEdit')
            ->set('descriptionInput', $updated)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame($updated, ArchivedLog::query()->find($archivedLog->id)->description);
    }

    public function test_non_owner_cannot_update_description(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $owner->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived message',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($other)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->set('descriptionInput', 'Intento no autorizado')
            ->call('save')
            ->assertForbidden();

        $this->assertNull(ArchivedLog::query()->find($archivedLog->id)->description);
    }

    public function test_whitespace_only_description_is_saved_as_null(): void
    {
        $user = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived message',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->call('enableEdit')
            ->set('descriptionInput', '   ')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(ArchivedLog::query()->find($archivedLog->id)->description);
    }

    public function test_clearing_existing_description_saves_null(): void
    {
        $user = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived message',
            'metadata' => null,
            'description' => 'Texto original a borrar.',
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->call('enableEdit')
            ->set('descriptionInput', '')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertNull(ArchivedLog::query()->find($archivedLog->id)->description);
    }

    public function test_description_rejects_more_than_5000_characters(): void
    {
        $user = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived message',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->call('enableEdit')
            ->set('descriptionInput', str_repeat('a', 5001))
            ->call('save')
            ->assertHasErrors(['descriptionInput']);
    }

    public function test_active_log_detail_does_not_show_archived_description_section(): void
    {
        $user = User::factory()->create();
        [$application, $errorCode] = $this->seedApplicationAndErrorCode();

        $logId = DB::table('logs')->insertGetId([
            'error_code_id' => $errorCode->id,
            'application_id' => $application->id,
            'severity' => 'critical',
            'message' => 'Active log message',
            'file' => 'app/Foo.php',
            'line' => 1,
            'metadata' => json_encode([], JSON_THROW_ON_ERROR),
            'resolved' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/logs/'.$logId)
            ->assertOk()
            ->assertDontSee(__('archived_logs.description.section_title'));
    }

    /**
     * @return array{0: Application, 1: ErrorCode}
     */
    private function seedApplicationAndErrorCode(): array
    {
        $application = Application::query()->create([
            'name' => 'Desc Test App',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'DESC-1',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        return [$application, $errorCode];
    }
}
