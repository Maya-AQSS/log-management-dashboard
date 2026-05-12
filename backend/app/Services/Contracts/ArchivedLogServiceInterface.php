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
     * @param  int  $actorUserId
     */
    public function updateArchivedFields(ArchivedLog $archivedLog, array $fields, int $actorUserId): void;

    /**
     * Elimina (soft delete) un log archivado.
     *
     * @param  int  $actorUserId
     */
    public function delete(ArchivedLog $archivedLog, int $actorUserId): void;

    /**
     * Archiva un log por su id.
     *
     * @param  int  $logId
     * @param  int  $archivedById
     */
    public function archiveFromLogId(int $logId, int $archivedById): ArchivedLog;
}
