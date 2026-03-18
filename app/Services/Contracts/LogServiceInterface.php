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
     * Buscar y filtrar (solo logs activos).
     */
    public function searchAndFilter(
        ?string $search,
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Counts grouped by severity, incluyendo zeros (solo logs activos).
     *
     * @return array<string,int>
     */
    public function severityCounts(): array;

    /**
     * Devuelve el id de ArchivedLog asociado al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int;
}
