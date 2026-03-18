<?php

namespace App\Repositories\Eloquent;

use App\Models\ArchivedLog;
use App\Models\Log;
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
            $archivedLog->logs()->update(['matched_archived_log_id' => null]);
            $archivedLog->delete();
        });
    }

    public function archiveFromLogId(int $logId, int $archivedById): ArchivedLog
    {
        return DB::transaction(function () use ($logId, $archivedById): ArchivedLog {
            $log = Log::query()
                ->with(['errorCode'])
                ->whereKey($logId)
                ->whereNull('matched_archived_log_id') // solo activo
                ->firstOrFail();

            $archivedLog = ArchivedLog::query()->create([
                'application_id' => (int) $log->application_id,
                'archived_by_id' => (int) $archivedById,
                'error_code_id' => $log->error_code_id,
                'severity' => $log->severity,
                'message' => $log->message,
                'metadata' => $log->metadata,
                'description' => $log->errorCode?->description,
                'url_tutorial' => null,
                'original_created_at' => $log->created_at,
                'archived_at' => now(),
            ]);

            $updated = Log::query()
                ->whereKey($logId)
                ->whereNull('matched_archived_log_id')
                ->update(['matched_archived_log_id' => $archivedLog->id]);

            if ($updated !== 1) {
                // Evita inconsistencias si algo cambió entre lecturas
                $archivedLog->delete();
                abort(409, 'Log already archived.');
            }

            return $archivedLog;
        });
    }
}
