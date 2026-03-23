<?php

namespace App\Repositories\Contracts;

use App\Models\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface LogRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Log;

    /**
     * SSE: solo logs activos.
     */
    public function latestForStream(int $limit = 10): Collection;

    /**
     * Buscar y filtrar.
     */
    public function searchAndFilter(
        ?string $search,
        ?string $severity,
        ?string $archived,
        ?string $resolved,
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Counts grouped by severity y resolved.
     *
     * @return array<string,array{resolved:int,unresolved:int,total:int}>
     */
    public function severityResolvedCounts(bool $includeArchived = false): array;

    /**
     * Devuelve el id de ArchivedLog asociado al log (matched_archived_log_id) o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int;
}
