<?php

namespace App\Services;

use App\Models\ErrorCode;
use App\Repositories\Contracts\ErrorCodeRepositoryInterface;
use App\Services\Contracts\ErrorCodeServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ErrorCodeService implements ErrorCodeServiceInterface
{
    public function __construct(
        private ErrorCodeRepositoryInterface $errorCodeRepository
    ) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->errorCodeRepository->paginate($perPage);
    }

    public function searchAndFilter(
        ?string $search,
        ?int $filterApp,
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->errorCodeRepository->searchAndFilter($search, $filterApp, $severity, $perPage);
    }

    public function findOrFail(int $id): ErrorCode
    {
        return $this->errorCodeRepository->findOrFail($id);
    }

    public function create(array $data): ErrorCode
    {
        return $this->errorCodeRepository->create($data);
    }

    public function update(ErrorCode $errorCode, array $data): ErrorCode
    {
        return $this->errorCodeRepository->update($errorCode, $data);
    }

    public function delete(ErrorCode $errorCode): void
    {
        $this->errorCodeRepository->delete($errorCode);
    }
}
