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

    /**
     * @param  array<string, mixed>  $fields
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): void;

    public function delete(ArchivedLog $archivedLog): void;

    /**
     * Archiva un log activo. `$archivedByUserId` es el subject del JWT (UUID Keycloak),
     * que se persiste en `archived_logs.archived_by_id` (no exige fila en la vista FDW `users`).
     */
    public function archiveFromLogId(int $logId, string $archivedByUserId): ArchivedLog;
}
