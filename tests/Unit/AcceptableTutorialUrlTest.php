<?php

namespace Tests\Unit;

use App\Rules\AcceptableTutorialUrl;
use Tests\TestCase;

class AcceptableTutorialUrlTest extends TestCase
{
    private function validate(string $value): ?string
    {
        $error = null;
        (new AcceptableTutorialUrl())->validate('url', $value, function (string $message) use (&$error): void {
            $error = $message;
        });

        return $error;
    }

    public function test_valid_https_url_with_dotted_domain_passes(): void
    {
        $this->assertNull($this->validate('https://docs.example.com/guide'));
    }

    public function test_valid_http_url_passes(): void
    {
        $this->assertNull($this->validate('http://wiki.internal.org/page'));
    }

    public function test_localhost_passes(): void
    {
        $this->assertNull($this->validate('http://localhost:8080/docs'));
    }

    public function test_ipv4_address_passes(): void
    {
        $this->assertNull($this->validate('http://192.168.1.100/guide'));
    }

    public function test_ipv6_address_passes(): void
    {
        $this->assertNull($this->validate('http://[::1]/docs'));
    }

    public function test_empty_string_passes(): void
    {
        $this->assertNull($this->validate(''));
    }

    public function test_whitespace_only_string_passes(): void
    {
        $this->assertNull($this->validate('   '));
    }

    public function test_single_label_host_is_rejected(): void
    {
        $this->assertNotNull($this->validate('https://example'));
    }

    public function test_ftp_scheme_is_rejected(): void
    {
        $this->assertNotNull($this->validate('ftp://docs.example.com/file'));
    }

    public function test_url_without_scheme_is_rejected(): void
    {
        $this->assertNotNull($this->validate('docs.example.com/guide'));
    }

    public function test_plain_string_is_rejected(): void
    {
        $this->assertNotNull($this->validate('no-es-una-url'));
    }

    public function test_url_with_empty_host_is_rejected(): void
    {
        $this->assertNotNull($this->validate('https:///path'));
    }
}
