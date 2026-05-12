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

    /**
     * @param  array<string, mixed>  $fields
     *
     * La autorización la define {@see \App\Policies\ArchivedLogPolicy} (subject JWT === `archived_by_id`).
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): void;

    /**
     * Soft delete. No valida actor; debe haberse pasado {@see \App\Policies\ArchivedLogPolicy}.
     */
    public function delete(ArchivedLog $archivedLog): void;

    /**
     * @param  string  $archivedByUserId  Subject JWT (UUID Keycloak) → `archived_logs.archived_by_id`.
     */
    public function archiveFromLogId(int $logId, string $archivedByUserId): ArchivedLog;
}
