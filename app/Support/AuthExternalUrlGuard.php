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
}
