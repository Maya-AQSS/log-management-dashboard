<?php

namespace App\Repositories\Eloquent;

use App\Models\ArchivedLog;
use App\Models\Log;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ArchivedLogRepository implements ArchivedLogRepositoryInterface
{
    /**
     * Devuelve una página de logs archivados.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->latest('archived_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Busca y filtra logs archivados por diferentes criterios:
     * - tipo de severidad de error
     * - si tiene tutorial o no
     */
    public function searchAndFilter(
        ?array $severities,
        ?int $applicationId,
        ?string $dateFrom,
        ?string $dateTo,
        int $perPage = 15
    ): LengthAwarePaginator {
        return ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->when($severities !== null && $severities !== [], fn ($q) => $q->whereIn('severity', $severities))
            ->when($applicationId !== null, fn ($q) => $q->where('application_id', $applicationId))
            ->when($dateFrom !== null, fn ($q) => $q->where('archived_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($q) => $q->where('archived_at', '<=', $dateTo))
            ->latest('archived_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Busca un log archivado por su id.
     */
    public function findOrFail(int $id): ArchivedLog
    {
        return ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->findOrFail($id);
    }

    /**
     * Elimina un log archivado.
     */
    public function delete(ArchivedLog $archivedLog): void
    {
        $archivedLog->delete();
    }

    /**
     * Archiva un log por su id.
     */
    public function archiveFromLogId(int $logId, int $archivedById): ArchivedLog
    {
        return DB::transaction(function () use ($logId, $archivedById): ArchivedLog {
            $log = Log::query()
                ->with(['errorCode'])
                ->whereKey($logId)
                ->firstOrFail();

            $existingArchived = ArchivedLog::query()
                ->where('application_id', $log->application_id)
                ->whereRaw('error_code_id IS NOT DISTINCT FROM ?', [$log->error_code_id])
                ->where('severity', $log->severity)
                ->where('message', $log->message)
                ->orderByDesc('archived_at')
                ->orderByDesc('id')
                ->first();

            if ($existingArchived !== null) {
                return $existingArchived;
            }

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

            return $archivedLog;
        });
    }
}
