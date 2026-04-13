<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueExternalIdTest extends TestCase
{
    use RefreshDatabase;

    public function test_two_users_cannot_share_the_same_external_id(): void
    {
        User::factory()->create(['external_id' => 'ext-shared-001']);

        $this->expectException(QueryException::class);

        User::factory()->create(['external_id' => 'ext-shared-001']);
    }

    public function test_multiple_users_with_null_external_id_are_allowed(): void
    {
        User::factory()->count(3)->create(['external_id' => null]);

        $this->assertDatabaseCount('users', 3);
    }
}
