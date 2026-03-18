<?php

namespace App\Repositories\Eloquent;

use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LogRepository implements LogRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Log::query()
            ->with(['application', 'errorCode', 'archivedLog'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): Log
    {
        return Log::query()
            ->with(['application', 'errorCode', 'archivedLog'])
            ->findOrFail($id);
    }

    public function latestForStream(int $limit = 10): Collection
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }
}
