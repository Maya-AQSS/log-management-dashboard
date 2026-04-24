<?php

namespace App\Support;

use RuntimeException;

final class AuthExternalUrlGuard
{
    public const DEFAULT_PLACEHOLDER_URL = 'http://auth.example.com';

    /**
     * Fail fast on deploy if external auth URL is missing or still the dev placeholder.
     * Local/testing keep using config default without blocking.
     */
    public static function assertConfiguredForDeploy(string $environment, string $url): void
    {
        if (! in_array($environment, ['production', 'staging'], true)) {
            return;
        }

        $normalized = rtrim(trim($url), '/');
        $placeholder = rtrim(self::DEFAULT_PLACEHOLDER_URL, '/');

        if ($normalized === '' || $normalized === $placeholder) {
            throw new RuntimeException(
                'AUTH_EXTERNAL_URL must be set to a real auth service base URL in production and staging. '.
                'The default placeholder ('.self::DEFAULT_PLACEHOLDER_URL.') is not allowed.'
            );
        }
    }

    /**
     * Fail fast on deploy if the API key is missing.
     * An empty key would cause all auth requests to be sent without credentials.
     */
    public static function assertApiKeyConfiguredForDeploy(string $environment, string $apiKey): void
    {
        if (! in_array($environment, ['production', 'staging'], true)) {
            return;
        }

        if (trim($apiKey) === '') {
            throw new RuntimeException(
                'AUTH_EXTERNAL_API_KEY must be set in production and staging. '.
                'Without it, requests to the external auth service are sent without credentials.'
            );
        }
    }
}
