<?php

namespace App\Listeners;

use App\Events\LogWasArchived;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Maya\Messaging\Publishers\AuditPublisher;

/**
 * Publica el evento de auditoría cuando un log activo se archiva (alineado con DMS: listener tras commit).
 */
final class RecordArchivedLogArchiveAudit implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly AuditPublisher $auditPublisher,
    ) {}

    public function handle(LogWasArchived $event): void
    {
        $slug = (string) config('messaging.app');

        $this->auditPublisher->publish(
            applicationSlug: $slug,
            entityType: 'archived_log',
            entityId: (string) $event->archivedLog->id,
            action: 'archive',
            userId: $event->archivedByUserId,
        );
    }
}
