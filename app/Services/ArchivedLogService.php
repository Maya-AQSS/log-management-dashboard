<?php

namespace App\Services;

use App\Models\ArchivedLog;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use App\Services\Contracts\ArchivedLogServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ArchivedLogService implements ArchivedLogServiceInterface
{
    public function __construct(
        private ArchivedLogRepositoryInterface $archivedLogRepository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->archivedLogRepository->paginate($perPage);
    }

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

    public function findOrFail(int $id): ArchivedLog
    {
        return $this->archivedLogRepository->findOrFail($id);
    }

    /**
     * Sanitizes string fields (trim + blank→null) before persisting.
     *
     * @param  array<string, mixed>  $fields
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): void
    {
        $sanitized = array_map(
            static fn ($value) => is_string($value) ? (blank($value) ? null : trim($value)) : $value,
            $fields
        );

        $this->archivedLogRepository->updateArchivedFields($archivedLog, $sanitized);
    }

    public function delete(ArchivedLog $archivedLog): void
    {
        $this->archivedLogRepository->delete($archivedLog);
    }

    public function archiveFromLogId(int $logId, int $archivedById): ArchivedLog
    {
        return $this->archivedLogRepository->archiveFromLogId($logId, $archivedById);
    }
}
