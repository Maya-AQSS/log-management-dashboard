<?php

namespace App\Services\Contracts;

use App\Models\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LogServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Log;

    /**
     * Prepare SSE payload.
     */
    public function streamPayload(int $limit = 10): array;

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
     * Data de cards del dashboard con estado resolved/unresolved.
     *
     * @return array<int,array{key:string,count:int,resolvedCount:int,unresolvedCount:int,routeParams:array<string,string>}>
     */
    public function dashboardSeverityCards(bool $includeArchived = false): array;

    /**
     * Devuelve el id de ArchivedLog asociado al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int;
}
