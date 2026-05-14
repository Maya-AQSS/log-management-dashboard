<?php

namespace App\Services;

use App\Models\ArchivedLog;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Services\Contracts\ArchivedLogServiceInterface;
use App\Support\ResilientLogPublisher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Throwable;

class ArchivedLogService implements ArchivedLogServiceInterface
{
    public function __construct(
        private ArchivedLogRepositoryInterface $archivedLogRepository,
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
                'LAR-LOG-004',
                ['archived_log_id' => $id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Actualiza los campos de un log archivado.
     *
     * El actor en auditoría lo define el modelo (`archived_by_id`) vía {@see ArchivedLogObserver}.
     * Si los valores ya coinciden con lo enviado (no-op / doble envío), no se persiste (el observer
     * no recibe `updated`).
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): void
    {
        try {
            $sanitized = array_map(
                static fn ($value) => is_string($value) ? (blank($value) ? null : trim($value)) : $value,
                $fields
            );

            if ($sanitized === [] || ! $this->archivedLogSanitizedDiffersFromModel($archivedLog, $sanitized)) {
                return;
            }

            $this->archivedLogRepository->updateArchivedFields($archivedLog, $sanitized);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'LAR-LOG-001',
                ['archived_log_id' => $archivedLog->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * Elimina un log archivado.
     *
     * Si {@see ArchivedLogRepositoryInterface::delete} no aplica borrado, no hay evento `deleted`
     * en el modelo y el observer no publica auditoría.
     */
    public function delete(ArchivedLog $archivedLog): void
    {
        try {
            if (! $this->archivedLogRepository->delete($archivedLog)) {
                return;
            }
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'LAR-LOG-002',
                ['archived_log_id' => $archivedLog->id],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $sanitized
     */
    private function archivedLogSanitizedDiffersFromModel(ArchivedLog $archivedLog, array $sanitized): bool
    {
        foreach ($sanitized as $key => $value) {
            if ($archivedLog->getAttribute($key) != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Archiva un log por su id.
     *
     * Si el repositorio devuelve un {@see ArchivedLog} ya existente (misma huella), no hay `created`
     * nuevo en Eloquent y el observer no duplica auditoría.
     */
    public function archiveFromLogId(int $logId, string $archivedByUserId): ArchivedLog
    {
        try {
            return $this->archivedLogRepository->archiveFromLogId($logId, $archivedByUserId);
        } catch (Throwable $e) {
            $this->resilientLogPublisher->publishFromThrowable(
                $e,
                'medium',
                'LAR-LOG-003',
                ['log_id' => $logId],
                $this->messagingAppSlug(),
            );
            throw $e;
        }
    }
}
