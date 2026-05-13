<?php

namespace App\Services\Contracts;

use App\Models\ArchivedLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ArchivedLogServiceInterface
{
    /**
     * Devuelve una página de logs archivados.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

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
    ): LengthAwarePaginator;

    /**
     * Busca un log archivado por su id.
     */
    public function findOrFail(int $id): ArchivedLog;

    /**
     * Actualiza los campos editables del log archivado.
     *
     * @param  array<string, mixed>  $fields
     *
     * La autorización (subject JWT === `archived_by_id`) la define {@see \App\Policies\ArchivedLogPolicy}.
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields): void;

    /**
     * Soft delete. Quién puede invocarlo lo define {@see \App\Policies\ArchivedLogPolicy}.
     */
    public function delete(ArchivedLog $archivedLog): void;

    /**
     * Archiva un log activo. `$archivedByUserId` es el subject del JWT (UUID Keycloak),
     * que se persiste en `archived_logs.archived_by_id` (no exige fila en la vista FDW `users`).
     */
    public function archiveFromLogId(int $logId, string $archivedByUserId): ArchivedLog;
}
