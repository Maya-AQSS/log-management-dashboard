<?php

namespace App\Services;

use App\Events\ArchivedLogWasDeleted;
use App\Events\LogWasArchived;
use App\Models\ArchivedLog;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Support\ResilientLogPublisher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Maya\Messaging\Publishers\AuditPublisher;
use Throwable;

class ArchivedLogService implements ArchivedLogServiceInterface
{
    public function __construct(
        private ArchivedLogRepositoryInterface $archivedLogRepository,
        private AuditPublisher $auditPublisher,
        private ResilientLogPublisher $resilientLogPublisher,
    ) {}

    private function messagingAppSlug(): string
    {
        return (string) config('messaging.app');
    }

    /**
     * Devuelve una página de logs archivados.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->archivedLogRepository->paginate($perPage);
    }

    /**
     * Busca y filtra logs archivados por diferentes criterios:
     * - tipo de severidad de error
     * - si tiene tutorial o no
     */
    public function searchAndFilter(
        ?array $severities,
        ?int $applicationId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortBy,
        string $sortDir,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->archivedLogRepository->searchAndFilter(
            $severities,
            $applicationId,
            $dateFrom,
            $dateTo,
            $sortBy,
            $sortDir,
            $perPage
        );
    }

    /**
     * Busca un log archivado por su id.
     *
     * Sin auditoría en cada GET (evita ruido); solo se publica a maya.logs si falla la carga.
     */
    public function findOrFail(int $id): ArchivedLog
    {
        try {
            return $this->archivedLogRepository->findOrFail($id);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_not_found',
                ['archived_log_id' => $id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Actualiza los campos de un log archivado.
     *
     * El actor en auditoría es `archived_by_id` (subject JWT), coherente con {@see ArchivedLogPolicy}.
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): void
    {
        try {
            $sanitized = array_map(
                static fn ($value) => is_string($value) ? (blank($value) ? null : trim($value)) : $value,
                $fields
            );

            $previousValue = [];
            foreach (array_keys($sanitized) as $key) {
                $previousValue[$key] = $archivedLog->getAttribute($key);
            }

            $this->archivedLogRepository->updateArchivedFields($archivedLog, $sanitized);
            $this->auditPublisher->publish(
                applicationSlug: $this->messagingAppSlug(),
                entityType: 'archived_log',
                entityId: (string) $archivedLog->id,
                action: 'update',
                userId: (string) $archivedLog->archived_by_id,
                previousValue: $previousValue !== [] ? $previousValue : null,
                newValue: $sanitized !== [] ? $sanitized : null,
            );
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_update_failed',
                ['archived_log_id' => $archivedLog->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Elimina un log archivado.
     */
    public function delete(ArchivedLog $archivedLog): void
    {
        try {
            $archivedLogId = $archivedLog->id;
            $archivedByUserId = (string) $archivedLog->archived_by_id;

            $this->archivedLogRepository->delete($archivedLog);

            ArchivedLogWasDeleted::dispatch($archivedLogId, $archivedByUserId);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_delete_failed',
                ['archived_log_id' => $archivedLog->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Archiva un log por su id.
     */
    public function archiveFromLogId(int $logId, string $archivedByUserId): ArchivedLog
    {
        try {
            $archivedLog = $this->archivedLogRepository->archiveFromLogId($logId, $archivedByUserId);
            LogWasArchived::dispatch($archivedLog, $archivedByUserId);

            return $archivedLog;
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'archived_log_archive_failed',
                ['log_id' => $logId],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }
}
