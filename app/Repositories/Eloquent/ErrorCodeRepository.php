<?php

namespace App\Repositories\Eloquent;

use App\Models\ErrorCode;
use App\Repositories\Contracts\ErrorCodeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ErrorCodeRepository implements ErrorCodeRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ErrorCode::query()
            ->with('application')
            ->withCount(['logs', 'archivedLogs', 'comments'])
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function searchAndFilter(
        ?string $severity,
        int $perPage = 15
    ): LengthAwarePaginator {
        return ErrorCode::query()
            ->with('application')
            ->withCount(['logs', 'archivedLogs', 'comments'])
            ->when($severity, fn ($q) => $q->where('severity', $severity))
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOrFail(int $id): ErrorCode
    {
        return ErrorCode::query()
            ->with('application')
            ->findOrFail($id);
    }
}
