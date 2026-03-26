<?php

namespace App\Repositories\Eloquent;

use App\Models\ArchivedLog;
use App\Models\Log;
use App\Repositories\Contracts\ArchivedLogRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ArchivedLogRepository implements ArchivedLogRepositoryInterface
{
    private const SORT_DIRECTIONS = ['asc', 'desc'];

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
        ?string $sortBy,
        string $sortDir,
        int $perPage = 15
    ): LengthAwarePaginator {
        $validatedSortDirection = in_array($sortDir, self::SORT_DIRECTIONS, true) ? $sortDir : 'asc';

        $query = ArchivedLog::query()
            ->with(['application', 'archivedBy', 'errorCode'])
            ->withCount('comments')
            ->when($severities !== null && $severities !== [], fn ($q) => $q->whereIn('severity', $severities))
            ->when($applicationId !== null, fn ($q) => $q->where('application_id', $applicationId))
            ->when($dateFrom !== null, fn ($q) => $q->where('archived_at', '>=', $dateFrom))
            ->when($dateTo !== null, fn ($q) => $q->where('archived_at', '<=', $dateTo));

        match ($sortBy) {
            'archived_at' => $query
                ->orderBy('archived_at', $validatedSortDirection)
                ->orderByDesc('id'),
            'severity' => $this->applySeverityRankOrder($query, $validatedSortDirection),
            default => $query
                ->orderBy('archived_at', 'desc')
                ->orderByDesc('id'),
        };

        return $query
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Orden de negocio: critical → high → medium → low → other (ASC).
     * DESC invierte ese ranking.
     */
    private function applySeverityRankOrder(Builder $query, string $direction): void
    {
        $dir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $query->orderByRaw(
            'CASE severity WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 WHEN ? THEN 4 WHEN ? THEN 5 ELSE 99 END '.$dir,
            ['critical', 'high', 'medium', 'low', 'other']
        );
        $query->orderByDesc('archived_at');
        $query->orderByDesc('id');
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

    public function updateUrlTutorial(ArchivedLog $archivedLog, ?string $url): void
    {
        $archivedLog->url_tutorial = $url;
        $archivedLog->save();
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
