<?php

namespace App\Services\Contracts;

use App\Models\ErrorCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ErrorCodeServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function searchAndFilter(
        ?string $search,
        ?int $filterApp,
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findOrFail(int $id): ErrorCode;

    public function create(array $data): ErrorCode;

    public function update(ErrorCode $errorCode, array $data): ErrorCode;

    public function delete(ErrorCode $errorCode): void;
}
