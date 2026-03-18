<?php

namespace App\Services\Contracts;

use App\Models\Log;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface LogServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findOrFail(int $id): Log;

    /**
     * Prepare SSE payload (already mapped to arrays for response).
     */
    public function streamPayload(int $limit = 10): array;

    /**
     * Counts grouped by severity, including zeros for all enum values.
     *
     * @return array<string,int>
     */
    public function severityCounts(): array;
}
