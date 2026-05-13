<?php

namespace App\Listeners;

use App\Events\ArchivedLogWasDeleted;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Maya\Messaging\Publishers\AuditPublisher;

/**
 * Registra en auditoría el borrado de un log archivado (tras commit de la operación).
 */
final class RecordArchivedLogDeleteAudit implements ShouldHandleEventsAfterCommit
{
    public function __construct(
        private readonly AuditPublisher $auditPublisher,
    ) {}

    public function handle(ArchivedLogWasDeleted $event): void
    {
        $slug = (string) config('messaging.app');

        $this->auditPublisher->publish(
            applicationSlug: $slug,
            entityType: 'archived_log',
            entityId: (string) $event->archivedLogId,
            action: 'delete',
            userId: $event->archivedByUserId,
        );
    }
}
