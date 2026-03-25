<?php

namespace App\Services\Contracts;

use App\Models\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LogServiceInterface
{
    /**
     * Devuelve una página de logs.
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator;

    /**
     * Encuentra un log por su id.
     */
    public function findOrFail(int $id): Log;

    /**
     * Prepare SSE payload.
     */
    public function streamPayload(int $limit = 10): array;

    /**
     * Busca y filtra logs.
     */
    public function searchAndFilter(
        ?string $search,
        ?array $severity,
        ?string $archived,
        ?string $resolved,
        ?string $dateFrom,
        ?string $dateTo,
        int $perPage = 25
    ): LengthAwarePaginator;

    /**
     * Devuelve los datos de las cards del dashboard con estado resolved/unresolved.
     * Incluye todas las severidades y la card "all" usando el total de logs.
     *
     * @return array<int,array{key:string,totalCount:int,resolvedCount:int,unresolvedCount:int}>
     */
    public function dashboardSeverityCards(): array;

    /**
     * Devuelve el id de ArchivedLog equivalente al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int;
}
