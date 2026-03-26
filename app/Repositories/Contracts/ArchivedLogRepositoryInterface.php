<?php

namespace App\Repositories\Contracts;

use App\Models\ArchivedLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArchivedLogRepositoryInterface
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

    public function updateUrlTutorial(ArchivedLog $archivedLog, ?string $url): void;

    public function updateDescription(ArchivedLog $archivedLog, ?string $description): void;

    public function delete(ArchivedLog $archivedLog): void;

    public function archiveFromLogId(int $logId, int $archivedById): ArchivedLog;
}
