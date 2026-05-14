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
        ?int $applicationId,
        ?string $archived,
        ?string $resolved,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $sortBy,
        ?string $sortDir,
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
     * Conteos de logs por aplicación (misma caché de agregados que las cards de severidad).
     *
     * @return array<int, array{application_id: int, name: string, total: int}>
     */
    public function dashboardApplicationTotals(): array;

    /**
     * Devuelve el id de ArchivedLog equivalente al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int;

    /**
     * Marca el log como resuelto. Publica auditoría solo si hubo cambio en BD.
     *
     * @param  string  $actorUserId  Subject JWT (UUID) del usuario que resuelve.
     */
    public function resolved(int $logId, string $actorUserId): void;
}
