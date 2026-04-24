<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

class AuthGatewayLoggingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.auth_gateway.external_url' => 'http://auth.example.com']);
    }

    public function test_logs_debug_when_guest_request_has_no_session_token(): void
    {
        /** @var LegacyMockInterface $spy */
        $spy = Log::spy();

        $this->get('/');

        $spy->shouldHaveReceived('debug')->withArgs(function (string $message, array $context) {
            return str_contains($message, 'AuthGateway')
                && ($context['reason'] ?? null) === 'no_session_token';
        });
    }

    public function test_logs_warning_when_session_token_present_but_validate_returns_non_success(): void
    {
        /** @var LegacyMockInterface $spy */
        $spy = Log::spy();

        Http::fake([
            'http://auth.example.com/*' => Http::response(['error' => 'invalid'], 401),
        ]);

        $this->get('/?session_token=invalid-token');

        $spy->shouldHaveReceived('warning')->withArgs(function (string $message, array $context) {
            return str_contains($message, 'AuthGateway')
                && ($context['reason'] ?? null) === 'after_external_validate_attempt'
                && ($context['failure_reason'] ?? null) === 'external_validate_http_not_successful'
                && ($context['http_status'] ?? null) === 401;
        });
    }

    public function test_already_authenticated_user_skips_gateway_validation(): void
    {
        $user = User::factory()->create();

        Http::fake(); // no HTTP calls expected

        $this->actingAs($user)
            ->withCookie('session_token', 'some-token')
            ->get('/');

        Http::assertNothingSent();
    }

    public function test_missing_external_id_in_payload_logs_warning(): void
    {
        /** @var LegacyMockInterface $spy */
        $spy = Log::spy();

        Http::fake([
            'http://auth.example.com/*' => Http::response(['user' => ['name' => 'no-id-here']], 200),
        ]);

        $this->get('/?session_token=token-no-id');

        $spy->shouldHaveReceived('warning')->withArgs(function (string $message, array $context) {
            return str_contains($message, 'AuthGateway')
                && ($context['failure_reason'] ?? null) === 'missing_external_id_in_payload';
        });
    }

    public function test_unknown_external_id_creates_new_user_and_authenticates(): void
    {
        Http::fake([
            'http://auth.example.com/*' => Http::response([
                'user' => ['id' => 'ext-brand-new-999', 'name' => 'New User', 'email' => 'new@example.com'],
            ], 200),
        ]);

        $this->assertDatabaseMissing('users', ['external_id' => 'ext-brand-new-999']);

        $this->get('/?session_token=brand-new-token');

        $this->assertDatabaseHas('users', [
            'external_id' => 'ext-brand-new-999',
            'name' => 'New User',
            'email' => 'new@example.com',
        ]);
    }

    public function test_stale_cache_entry_is_evicted_when_user_is_deleted(): void
    {
        $user = User::factory()->create(['external_id' => 'ext-deleted-user']);
        $token = 'token-for-deleted-user';
        $cacheKey = 'auth_gateway:'.hash('sha256', $token);

        // Pre-seed cache as if a previous request had authenticated this user.
        Cache::put($cacheKey, $user->id, 60);

        // User is deleted before the next request arrives.
        $user->delete();

        Http::fake([
            'http://auth.example.com/*' => Http::response(['error' => 'unauthorized'], 401),
        ]);

        // The middleware must evict the stale cache entry and fall through to the external service.
        $this->get('/?session_token='.$token);

        $this->assertNull(Cache::get($cacheKey));
    }

    public function test_connection_exception_logs_warning_with_exception_reason(): void
    {
        /** @var LegacyMockInterface $spy */
        $spy = Log::spy();

        Http::fake(function () {
            throw new ConnectionException('Connection timed out after 3 seconds');
        });

        $this->get('/?session_token=timeout-prone-token');

        $spy->shouldHaveReceived('warning')->withArgs(function (string $message, array $context) {
            return str_contains($message, 'AuthGateway')
                && ($context['reason'] ?? null) === 'after_external_validate_attempt'
                && ($context['failure_reason'] ?? null) === 'exception_during_validate'
                && isset($context['exception_message']);
        });
    }

    public function test_valid_token_authenticates_user_and_caches_result(): void
    {
        $user = User::factory()->create(['external_id' => 'ext-user-42']);

        Http::fake([
            'http://auth.example.com/*' => Http::response([
                'user' => ['id' => 'ext-user-42', 'name' => 'Test User', 'email' => 'test@example.com'],
            ], 200),
        ]);

        $token = 'valid-token-for-ext-42';

        $this->get('/?session_token='.$token);

        // Cache must be populated after successful auth.
        $cacheKey = 'auth_gateway:'.hash('sha256', $token);
        $this->assertSame($user->id, Cache::get($cacheKey));

        // Second request must hit cache and skip HTTP.
        Http::fake(); // reset — no further calls expected

        $this->get('/?session_token='.$token);

        Http::assertNothingSent();
    }
}
