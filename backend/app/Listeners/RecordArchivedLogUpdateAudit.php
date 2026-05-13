<?php

namespace App\Listeners;

use App\Events\ArchivedLogFieldsWereUpdated;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Maya\Messaging\Publishers\AuditPublisher;

/**
 * Registra en auditoría la actualización de campos editables de un log archivado.
 */
final class RecordArchivedLogUpdateAudit implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly AuditPublisher $auditPublisher,
    ) {}

    public function handle(ArchivedLogFieldsWereUpdated $event): void
    {
        $slug = (string) config('messaging.app');

        $this->auditPublisher->publish(
            applicationSlug: $slug,
            entityType: 'archived_log',
            entityId: (string) $event->archivedLogId,
            action: 'update.fields',
            userId: $event->archivedByUserId,
            previousValue: $event->previousValue !== [] ? $event->previousValue : null,
            newValue: $event->newValue !== [] ? $event->newValue : null,
        );
    }
}
