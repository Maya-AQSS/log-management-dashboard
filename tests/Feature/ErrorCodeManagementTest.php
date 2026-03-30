<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Comment;
use App\Models\ErrorCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorCodeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_error_code_and_is_redirected_to_detail_with_flash(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'App Create',
            'description' => 'App for create test',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->post('/error-codes', [
                'application_id' => $application->id,
                'code' => 'E-101',
                'name' => 'Created error',
                'description' => 'Optional description',
                'file' => 'app/Services/Example.php',
                'line' => 42,
            ]);

        $errorCode = ErrorCode::query()->where('code', 'E-101')->firstOrFail();

        $response
            ->assertRedirect(route('error-codes.show', $errorCode->id))
            ->assertSessionHas('status', __('error_codes.created'));

        $this->assertDatabaseHas('error_codes', [
            'id' => $errorCode->id,
            'application_id' => $application->id,
            'code' => 'E-101',
            'name' => 'Created error',
        ]);
    }

    public function test_create_rejects_duplicate_composite_code_and_application(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'App Duplicate',
            'description' => 'App for duplicate test',
            'created_at' => now(),
        ]);

        ErrorCode::query()->create([
            'application_id' => $application->id,
            'code' => 'E-777',
            'name' => 'Original error',
        ]);

        $this->actingAs($user)
            ->from(route('error-codes.create'))
            ->post('/error-codes', [
                'application_id' => $application->id,
                'code' => 'E-777',
                'name' => 'Duplicate error',
            ])
            ->assertRedirect(route('error-codes.create'))
            ->assertSessionHasErrors([
                'code' => __('error_codes.validation.code_unique'),
            ]);

        $this->assertSame(
            1,
            ErrorCode::query()->where('application_id', $application->id)->where('code', 'E-777')->count()
        );
    }

    public function test_field_validations_match_acceptance_criteria(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'App Validation',
            'description' => 'App for validation test',
            'created_at' => now(),
        ]);

        $this->actingAs($user)
            ->from(route('error-codes.create'))
            ->post('/error-codes', [
                'application_id' => $application->id,
                'code' => str_repeat('A', 51),
                'name' => str_repeat('N', 201),
                'description' => str_repeat('D', 5001),
                'file' => str_repeat('F', 256),
                'line' => 0,
            ])
            ->assertRedirect(route('error-codes.create'))
            ->assertSessionHasErrors(['code', 'name', 'description', 'file', 'line']);

        $this->assertDatabaseCount('error_codes', 0);
    }

    public function test_user_can_update_error_code_from_detail_flow(): void
    {
        $user = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'App A',
            'description' => 'App for update test',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'application_id' => $application->id,
            'code' => 'E-400',
            'name' => 'Original name',
            'description' => 'Original description',
            'severity' => 'high',
            'file' => 'app/File.php',
            'line' => 10,
        ]);

        $this->actingAs($user)
            ->put('/error-codes/' . $errorCode->id, [
                'errorCodeId' => $errorCode->id,
                'application_id' => $application->id,
                'code' => 'E-400',
                'name' => 'Updated name',
                'description' => 'Updated description',
                'severity' => 'critical',
                'file' => 'app/UpdatedFile.php',
                'line' => 20,
            ])
            ->assertRedirect(route('error-codes.show', $errorCode->id))
            ->assertSessionHas('status', __('error_codes.updated'));

        $this->assertDatabaseHas('error_codes', [
            'id' => $errorCode->id,
            'application_id' => $application->id,
            'code' => 'E-400',
            'name' => 'Updated name',
            'description' => 'Updated description',
            'severity' => 'critical',
            'file' => 'app/UpdatedFile.php',
            'line' => 20,
        ]);
    }

    public function test_delete_error_code_cascades_comments_and_redirects_to_index(): void
    {
        $owner = User::factory()->create();

        $application = Application::query()->create([
            'name' => 'App B',
            'description' => 'App for delete test',
            'created_at' => now(),
        ]);

        $errorCode = ErrorCode::query()->create([
            'application_id' => $application->id,
            'code' => 'E-500',
            'name' => 'Delete me',
            'severity' => 'medium',
        ]);

        $comment = Comment::query()->create([
            'commentable_type' => ErrorCode::class,
            'commentable_id' => $errorCode->id,
            'user_id' => $owner->id,
            'content' => 'Comment tied to error code',
        ]);

        $this->actingAs($owner)
            ->delete('/error-codes/' . $errorCode->id)
            ->assertRedirect(route('error-codes.index'))
            ->assertSessionHas('status', __('error_codes.deleted'));

        $this->assertDatabaseMissing('error_codes', [
            'id' => $errorCode->id,
        ]);

        $this->assertDatabaseMissing('comments', [
            'id' => $comment->id,
        ]);
    }

        public function test_user_cannot_delete_other_users_error_code()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $errorCode = ErrorCode::factory()->create([
            'created_by_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->delete(route('error-codes.destroy', $errorCode->id))
            ->assertForbidden();
    }

        public function test_user_can_delete_own_error_code()
    {
        $user = User::factory()->create();

        $errorCode = ErrorCode::factory()->create([
            'created_by_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete(route('error-codes.destroy', $errorCode->id))
            ->assertRedirect();
    }


}
