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
        ?string $severity,
        ?string $tutorial,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->archivedLogRepository->searchAndFilter($severity, $tutorial, $perPage);
    }

    public function findOrFail(int $id): ArchivedLog
    {
        return $this->archivedLogRepository->findOrFail($id);
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
