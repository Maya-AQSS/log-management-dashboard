<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Listeners\PublishTelemetryOnAuditPublishFailure;
use App\Support\ResilientLogPublisher;
use Illuminate\Log\Events\MessageLogged;
use Maya\Messaging\Publishers\LogPublisher;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PublishTelemetryOnAuditPublishFailureTest extends TestCase
{
    #[Test]
    public function publica_lar_log_020_en_audit_publish_failed(): void
    {
        config(['messaging.app' => 'maya-logs']);

        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->once())
            ->method('publish')
            ->with(
                'high',
                'broker down',
                'LAR-LOG-020',
                $this->anything(),
                $this->anything(),
                [
                    'exchange' => 'maya.audit',
                    'application_slug' => 'maya-logs',
                    'entity_type' => 'log',
                    'entity_id' => '42',
                    'action' => 'Marcar un log como resuelto',
                    'user_id' => 'sub-1',
                ],
                'maya-logs',
            );

        $listener = new PublishTelemetryOnAuditPublishFailure(
            new ResilientLogPublisher($logPublisher),
        );

        $listener->handle(new MessageLogged('warning', 'audit.publish_failed', [
            'exchange' => 'maya.audit',
            'application_slug' => 'maya-logs',
            'entity_type' => 'log',
            'entity_id' => '42',
            'action' => 'Marcar un log como resuelto',
            'user_id' => 'sub-1',
            'error' => 'broker down',
        ]));
    }

    #[Test]
    public function ignora_otros_mensajes_de_log(): void
    {
        $logPublisher = $this->createMock(LogPublisher::class);
        $logPublisher->expects($this->never())->method('publish');

        $listener = new PublishTelemetryOnAuditPublishFailure(
            new ResilientLogPublisher($logPublisher),
        );

        $listener->handle(new MessageLogged('warning', 'other.message', []));
        $listener->handle(new MessageLogged('error', 'audit.publish_failed', []));
    }
}
