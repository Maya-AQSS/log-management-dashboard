<?php

namespace App\Services\Contracts;

use App\Models\ErrorCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ErrorCodeServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function searchAndFilter(
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findOrFail(int $id): ErrorCode;
}
