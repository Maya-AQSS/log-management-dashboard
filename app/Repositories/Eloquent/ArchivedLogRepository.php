<?php

namespace App\Repositories\Eloquent;

use App\Models\ArchivedLog;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ArchivedLogRepository implements ArchivedLogRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->latest('archived_at')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): ArchivedLog
    {
        return ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->findOrFail($id);
    }

    public function delete(ArchivedLog $archivedLog): void
    {
        DB::transaction(function () use ($archivedLog): void {
            // Al archivar se rompe la relación con el log original
            $archivedLog->logs()->update(['matched_archived_log_id' => null]);
            $archivedLog->delete();
        });
    }
}
