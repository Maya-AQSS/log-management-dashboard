<?php

namespace App\Services\Contracts;

use App\Models\ArchivedLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArchivedLogServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function searchAndFilter(
        ?array $severities,
        ?int $applicationId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortBy,
        string $sortDir,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findOrFail(int $id): ArchivedLog;

    public function updateUrlTutorial(ArchivedLog $archivedLog, ?string $url): ArchivedLog;

    public function delete(ArchivedLog $archivedLog): void;

    public function archiveFromLogId(int $logId, int $archivedById): ArchivedLog;
}
