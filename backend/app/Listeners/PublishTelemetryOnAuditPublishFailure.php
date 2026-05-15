<?php

namespace App\Listeners;

use App\Support\ResilientLogPublisher;
use Illuminate\Log\Events\MessageLogged;

/**
 * Cuando {@see \Maya\Messaging\Publishers\AuditPublisher} no puede publicar en maya.audit,
 * deja `Log::warning('audit.publish_failed', …)`. Este listener replica la incidencia en
 * maya.logs con LAR-LOG-020 para que aparezca en el panel (si el broker de logs responde).
 */
final class PublishTelemetryOnAuditPublishFailure
{
    private const TELEMETRY_CODE = 'LAR-LOG-020';

    public function __construct(
        private readonly ResilientLogPublisher $resilientLogPublisher,
    ) {}

    public function handle(MessageLogged $event): void
    {
        if ($event->level !== 'warning' || $event->message !== 'audit.publish_failed') {
            return;
        }

        $context = $event->context;
        $brokerError = $context['error'] ?? null;
        $message = is_string($brokerError) && $brokerError !== ''
            ? $brokerError
            : 'Fallo al publicar evento de auditoría en maya.audit';

        $this->resilientLogPublisher->publishStructured(
            'high',
            $message,
            self::TELEMETRY_CODE,
            [
                'exchange' => $context['exchange'] ?? null,
                'application_slug' => $context['application_slug'] ?? null,
                'entity_type' => $context['entity_type'] ?? null,
                'entity_id' => $context['entity_id'] ?? null,
                'action' => $context['action'] ?? null,
                'user_id' => $context['user_id'] ?? null,
            ],
            (string) config('messaging.app'),
        );
    }
}
