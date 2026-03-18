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
     * Logs list for SSE stream.
     */
    public function latestForStream(int $limit = 10): Collection;

    /**
     * Counts grouped by severity, including zeros for all enum values.
     *
     * @return array<string,int>
     */
    public function severityCounts(): array;
}
