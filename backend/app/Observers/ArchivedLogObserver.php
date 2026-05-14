<?php

namespace App\Observers;

use App\Models\ArchivedLog;
use Illuminate\Support\Facades\DB;
use Maya\Messaging\Publishers\AuditPublisher;

/**
 * Sin transacción activa, la publicación es inmediata; dentro de `DB::transaction`, se difiere al commit.
 * Actor: {@see ArchivedLog::$archived_by_id} (coherente con el panel JWT / policy).
 */
final class ArchivedLogObserver
{
    private const ENTITY_TYPE = 'archived_log';

    public function __construct(
        private readonly AuditPublisher $publisher,
    ) {}

    /**
     * Registra la auditoría de la creación de un log archivado.
     *
     * @param  ArchivedLog  $archivedLog  El log archivado que se está creando.
     */
    public function created(ArchivedLog $archivedLog): void
    {
        $this->afterCommit(fn () => $this->publish(
            'Archivar un log',
            $archivedLog,
            null,
            $archivedLog->getAttributes(),
        ));
    }

    /**
     * Registra la auditoría de la actualización de un log archivado.
     *
     * @param  ArchivedLog  $archivedLog  El log archivado que se está actualizando.
     */
    public function updated(ArchivedLog $archivedLog): void
    {
        $previous = $archivedLog->getPrevious();
        $new = $archivedLog->getChanges();

        $this->afterCommit(fn () => $this->publish(
            'Actualizar un log archivado',
            $archivedLog,
            $previous !== [] ? $previous : null,
            $new !== [] ? $new : null,
        ));
    }

    /**
     * Registra la auditoría de la eliminación de un log archivado.
     *
     * @param  ArchivedLog  $archivedLog  El log archivado que se está eliminando.
     */
    public function deleted(ArchivedLog $archivedLog): void
    {
        $this->afterCommit(fn () => $this->publish(
            'Eliminar un log archivado',
            $archivedLog,
            $archivedLog->getAttributes(),
            null,
        ));
    }

    /**
     * Con transacción activa, difiere al commit (equivalente a listeners `ShouldHandleEventsAfterCommit`).
     * Sin transacción, ejecuta de inmediato (tests, comandos sin `DB::transaction`).
     */
    private function afterCommit(callable $callback): void
    {
        if (DB::transactionLevel() === 0) {
            $callback();

            return;
        }

        DB::afterCommit($callback);
    }

    /**
     * Publica la auditoría de una acción sobre un log archivado.
     *
     * @param  string  $action  La acción que se está registrando.
     * @param  ArchivedLog  $archivedLog  El log archivado que se está registrando.
     * @param  array<string, mixed>|null  $previousValue  Los valores anteriores de los campos.
     * @param  array<string, mixed>|null  $newValue  Los nuevos valores de los campos.
     */
    private function publish(
        string $action,
        ArchivedLog $archivedLog,
        ?array $previousValue,
        ?array $newValue,
    ): void {
        $userId = (string) ($archivedLog->archived_by_id ?? 'system');

        $this->publisher->publish(
            applicationSlug: (string) config('messaging.app'),
            entityType: self::ENTITY_TYPE,
            entityId: (string) $archivedLog->getKey(),
            action: $action,
            userId: $userId,
            previousValue: $previousValue,
            newValue: $newValue,
        );
    }
}
