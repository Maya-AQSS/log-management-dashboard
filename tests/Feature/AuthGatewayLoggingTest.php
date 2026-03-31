<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\LegacyMockInterface;
use Tests\TestCase;

class AuthGatewayLoggingTest extends TestCase
{
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

        $this->withCookie('session_token', 'invalid-token')
            ->get('/');

        $spy->shouldHaveReceived('warning')->withArgs(function (string $message, array $context) {
            return str_contains($message, 'AuthGateway')
                && ($context['reason'] ?? null) === 'after_external_validate_attempt'
                && ($context['failure_reason'] ?? null) === 'external_validate_http_not_successful'
                && ($context['http_status'] ?? null) === 401;
        });
    }
}
