<?php

namespace App\Observers;

use App\Models\ArchivedLog;
use Illuminate\Database\Eloquent\Model;

/**
 * Actor: {@see ArchivedLog::$archived_by_id} (coherente con el panel JWT / policy).
 */
final class ArchivedLogObserver extends AbstractAuditableModelObserver
{
    /**
     * @return list<string>
     */
    protected function auditTemporalKeys(): array
    {
        return [
            'archived_at',
            'updated_at',
            'deleted_at',
            'original_created_at',
        ];
    }

    protected function auditEntityType(): string
    {
        return 'archived_log';
    }

    protected function resolveAuditUserId(Model $model): string
    {
        /** @var ArchivedLog $model */
        return (string) ($model->archived_by_id ?? 'system');
    }

    public function created(ArchivedLog $archivedLog): void
    {
        $this->auditAfterCreate('Archivar un log', $archivedLog);
    }

    public function updated(ArchivedLog $archivedLog): void
    {
        $this->auditAfterUpdate('Actualizar un log archivado', $archivedLog);
    }

    public function deleted(ArchivedLog $archivedLog): void
    {
        $this->auditAfterDelete('Eliminar un log archivado', $archivedLog);
    }

    protected function auditUpdateDiff(Model $model): array
    {
        /** @var ArchivedLog $model */
        $previous = $model->getPrevious();
        $new = $model->getChanges();

        return [
            $previous !== [] ? $previous : null,
            $new !== [] ? $new : null,
        ];
    }
}
