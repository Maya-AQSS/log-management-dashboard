<?php

namespace Tests\Feature;

use App\Livewire\ArchivedLogDetail;
use App\Models\Application;
use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArchivedLogSaveChangesTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_description_and_url_tutorial_together(): void
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

        $description = 'Causa raíz identificada y pasos de resolución.';
        $url = 'https://docs.example.com/how-to-fix';

        Livewire::actingAs($user)
            ->test(ArchivedLogDetail::class, ['archivedLogId' => $archivedLog->id])
            ->call('startEditingArchivedFields')
            ->assertSet('editingUrlTutorial', true)
            ->assertSet('descriptionPanelMode', 'editing')
            ->set('descriptionInput', $description)
            ->set('urlTutorialInput', $url)
            ->call('saveArchivedDetailChanges')
            ->assertHasNoErrors()
            ->assertSet('editingUrlTutorial', false)
            ->assertSet('descriptionPanelMode', 'closed');

        $fresh = ArchivedLog::query()->find($archivedLog->id);
        $this->assertSame($description, $fresh->description);
        $this->assertSame($url, $fresh->url_tutorial);
    }

    public function test_invalid_url_in_combined_save_shows_validation_error_and_does_not_persist(): void
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
            ->call('startEditingArchivedFields')
            ->set('descriptionInput', 'Descripción válida.')
            ->set('urlTutorialInput', 'https://single-label-host')
            ->call('saveArchivedDetailChanges')
            ->assertHasErrors(['urlTutorialInput'])
            ->assertSet('editingUrlTutorial', true);

        $fresh = ArchivedLog::query()->find($archivedLog->id);
        $this->assertNull($fresh->description);
        $this->assertNull($fresh->url_tutorial);
    }

    public function test_non_owner_cannot_save_combined_changes(): void
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
            ->set('editingUrlTutorial', true)
            ->set('descriptionPanelMode', 'editing')
            ->set('descriptionInput', 'Intento no autorizado')
            ->set('urlTutorialInput', 'https://docs.example.com/page')
            ->call('saveArchivedDetailChanges')
            ->assertForbidden();

        $fresh = ArchivedLog::query()->find($archivedLog->id);
        $this->assertNull($fresh->description);
        $this->assertNull($fresh->url_tutorial);
    }

    /**
     * @return array{0: Application, 1: ErrorCode}
     */
    private function seedApplicationAndErrorCode(): array
    {
        $application = Application::query()->create([
            'name' => 'Save Changes App',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'SAVE-1',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        return [$application, $errorCode];
    }
}
