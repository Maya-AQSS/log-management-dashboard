<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Observers\Concerns\NormalizesAuditTemporalPayload;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class NormalizesAuditTemporalPayloadTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_formats_eloquent_timestamps_in_configured_timezone(): void
    {
        config(['messaging.audit_timestamp_timezone' => 'Europe/Madrid']);
        Carbon::setTestNow(Carbon::parse('2026-05-15T08:20:47Z'));

        $normalizer = new class
        {
            use NormalizesAuditTemporalPayload;

            /** @param  list<string>  $keys */
            public function normalize(?array $payload, array $keys): ?array
            {
                return $this->normalizeAuditTemporalPayload($payload, $keys);
            }
        };

        $result = $normalizer->normalize(
            ['created_at' => '2026-05-15 08:20:47', 'content' => 'x'],
            ['created_at', 'updated_at'],
        );

        $this->assertSame('x', $result['content']);
        $this->assertStringContainsString('2026-05-15T10:20:47', (string) $result['created_at']);
        $this->assertStringContainsString('+', (string) $result['created_at']);
    }

    public function test_falls_back_to_utc_z_when_timezone_config_empty(): void
    {
        config(['messaging.audit_timestamp_timezone' => '']);
        Carbon::setTestNow(Carbon::parse('2026-05-15T08:20:47Z'));

        $normalizer = new class
        {
            use NormalizesAuditTemporalPayload;

            public function normalize(?array $payload): ?array
            {
                return $this->normalizeAuditTemporalPayload($payload, ['created_at']);
            }
        };

        $result = $normalizer->normalize(['created_at' => '2026-05-15 08:20:47']);

        $this->assertSame('2026-05-15T08:20:47Z', $result['created_at']);
    }
}
