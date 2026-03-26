<?php

namespace Tests\Feature;

use App\Livewire\LogDetail;
use App\Models\Application;
use App\Models\ArchivedLog;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArchivedLogUrlTutorialTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_save_valid_url_tutorial(): void
    {
        $user = User::factory()->create();
        $application = Application::query()->create([
            'name' => 'URL Tutorial App',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'URL-1',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        $url = 'https://docs.example.com/how-to-fix';

        Livewire::actingAs($user)
            ->test(LogDetail::class, ['source' => 'archived_log', 'recordId' => $archivedLog->id])
            ->set('urlTutorialInput', $url)
            ->call('updateUrlTutorial')
            ->assertHasNoErrors();

        $this->assertSame($url, ArchivedLog::query()->find($archivedLog->id)->url_tutorial);
    }

    public function test_invalid_url_shows_validation_error(): void
    {
        $user = User::factory()->create();
        $application = Application::query()->create([
            'name' => 'URL Tutorial App 2',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'URL-2',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(LogDetail::class, ['source' => 'archived_log', 'recordId' => $archivedLog->id])
            ->set('urlTutorialInput', 'no-es-una-url')
            ->call('updateUrlTutorial')
            ->assertHasErrors(['urlTutorialInput']);

        $this->assertNull(ArchivedLog::query()->find($archivedLog->id)->url_tutorial);
    }

    public function test_single_label_host_like_https_example_is_rejected(): void
    {
        $user = User::factory()->create();
        $application = Application::query()->create([
            'name' => 'URL Tutorial App SL',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'URL-SL',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $user->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($user)
            ->test(LogDetail::class, ['source' => 'archived_log', 'recordId' => $archivedLog->id])
            ->set('urlTutorialInput', 'https://example')
            ->call('updateUrlTutorial')
            ->assertHasErrors(['urlTutorialInput']);

        $this->assertNull(ArchivedLog::query()->find($archivedLog->id)->url_tutorial);
    }

    public function test_non_owner_cannot_update_url_tutorial(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'URL Tutorial App 3',
            'description' => 'Test',
            'created_at' => now(),
        ]);
        $errorCode = ErrorCode::query()->create([
            'code' => 'URL-3',
            'application_id' => $application->id,
            'name' => 'Error',
            'description' => 'D',
            'severity' => 'high',
        ]);

        $archivedLog = ArchivedLog::query()->create([
            'application_id' => $application->id,
            'archived_by_id' => $owner->id,
            'error_code_id' => $errorCode->id,
            'severity' => 'high',
            'message' => 'Archived',
            'metadata' => null,
            'description' => null,
            'url_tutorial' => null,
            'original_created_at' => now()->subMinute(),
            'archived_at' => now(),
        ]);

        Livewire::actingAs($other)
            ->test(LogDetail::class, ['source' => 'archived_log', 'recordId' => $archivedLog->id])
            ->set('urlTutorialInput', 'https://example.com/doc')
            ->call('updateUrlTutorial')
            ->assertForbidden();

        $this->assertNull(ArchivedLog::query()->find($archivedLog->id)->url_tutorial);
    }
}
