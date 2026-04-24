<?php

namespace Tests\Unit;

use App\Support\AuthExternalUrlGuard;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class AuthExternalUrlGuardTest extends TestCase
{
    public function test_throws_when_default_placeholder_in_production(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertConfiguredForDeploy(
            'production',
            AuthExternalUrlGuard::DEFAULT_PLACEHOLDER_URL
        );
    }

    public function test_throws_when_default_placeholder_in_staging(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertConfiguredForDeploy(
            'staging',
            AuthExternalUrlGuard::DEFAULT_PLACEHOLDER_URL
        );
    }

    public function test_throws_when_placeholder_has_trailing_slash_in_production(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertConfiguredForDeploy(
            'production',
            AuthExternalUrlGuard::DEFAULT_PLACEHOLDER_URL.'/'
        );
    }

    public function test_throws_when_url_empty_in_production(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertConfiguredForDeploy('production', '');
    }

    public function test_throws_when_url_whitespace_only_in_staging(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertConfiguredForDeploy('staging', " \t ");
    }

    public function test_does_not_throw_for_placeholder_in_local(): void
    {
        AuthExternalUrlGuard::assertConfiguredForDeploy(
            'local',
            AuthExternalUrlGuard::DEFAULT_PLACEHOLDER_URL
        );
        $this->assertTrue(true);
    }

    public function test_does_not_throw_for_placeholder_in_testing(): void
    {
        AuthExternalUrlGuard::assertConfiguredForDeploy(
            'testing',
            AuthExternalUrlGuard::DEFAULT_PLACEHOLDER_URL
        );
        $this->assertTrue(true);
    }

    public function test_does_not_throw_for_non_placeholder_url_in_production(): void
    {
        AuthExternalUrlGuard::assertConfiguredForDeploy('production', 'https://auth.company.example');
        $this->assertTrue(true);
    }

    public function test_does_not_throw_for_non_placeholder_url_in_staging(): void
    {
        AuthExternalUrlGuard::assertConfiguredForDeploy('staging', 'https://staging-auth.company.example/v1');
        $this->assertTrue(true);
    }

    // --- assertApiKeyConfiguredForDeploy ---

    public function test_api_key_throws_when_empty_in_production(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/AUTH_EXTERNAL_API_KEY/');
        AuthExternalUrlGuard::assertApiKeyConfiguredForDeploy('production', '');
    }

    public function test_api_key_throws_when_empty_in_staging(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertApiKeyConfiguredForDeploy('staging', '');
    }

    public function test_api_key_throws_when_whitespace_only_in_production(): void
    {
        $this->expectException(RuntimeException::class);
        AuthExternalUrlGuard::assertApiKeyConfiguredForDeploy('production', "   \t  ");
    }

    public function test_api_key_does_not_throw_for_valid_key_in_production(): void
    {
        AuthExternalUrlGuard::assertApiKeyConfiguredForDeploy('production', 'a-real-api-key-abc123');
        $this->assertTrue(true);
    }

    public function test_api_key_does_not_throw_in_local_environment(): void
    {
        AuthExternalUrlGuard::assertApiKeyConfiguredForDeploy('local', '');
        $this->assertTrue(true);
    }

    public function test_api_key_does_not_throw_in_testing_environment(): void
    {
        AuthExternalUrlGuard::assertApiKeyConfiguredForDeploy('testing', '');
        $this->assertTrue(true);
    }
}
