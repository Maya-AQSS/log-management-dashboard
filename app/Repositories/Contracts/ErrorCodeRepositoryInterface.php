<?php

namespace App\Repositories\Contracts;

use App\Models\ErrorCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ErrorCodeRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function searchAndFilter(
        ?string $search,
        ?int $filterApp,
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findOrFail(int $id): ErrorCode;
}
