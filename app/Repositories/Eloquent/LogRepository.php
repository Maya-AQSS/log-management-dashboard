<?php

namespace App\Repositories\Eloquent;

use App\Enums\Severity;
use App\Models\ArchivedLog;
use App\Models\Log;
use App\Repositories\Contracts\LogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LogRepository implements LogRepositoryInterface
{
    /**
     * Devuelve una página de logs.
     */
    public function paginate(int $perPage = 25): LengthAwarePaginator
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Busca un log por su id.
     */
    public function findOrFail(int $id): Log
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->findOrFail($id);
    }

    /**
     * Devuelve los últimos logs para streaming en tiempo real.
     */
    public function latestForStream(int $limit = 10): Collection
    {
        return Log::query()
            ->with(['application', 'errorCode'])
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Busca y filtra logs por diferentes criterios: 
     * - texto libre en el mensaje
     * - tipo de severidad de error
     * - si está archivado o no
     * - si está resuelto o no
     */
    public function searchAndFilter(
        ?string $search,
        ?string $severity,
        ?string $archived,
        ?string $resolved,
        int $perPage = 25
    ): LengthAwarePaginator
    {
        $archivedFlagSubquery = ArchivedLog::query()->selectRaw('1');
        $this->applyArchivedMatchForLogsQuery($archivedFlagSubquery);

        return Log::query()
            ->select('logs.*')
            ->addSelect([
                'is_archived' => $archivedFlagSubquery->limit(1),
            ])
            ->with(['application', 'errorCode'])
            ->when($search, fn ($q) => $q->where('message', 'ilike', '%' . $search . '%'))
            ->when($severity, fn ($q) => $q->where('severity', $severity))
            ->when($archived, function ($q) use ($archived): void {
                if ($archived === 'archived') {
                    $q->whereExists(fn ($subQuery) => $this->applyArchivedMatchForLogsQuery($subQuery));
                } elseif ($archived === 'not_archived') {
                    $q->whereNotExists(fn ($subQuery) => $this->applyArchivedMatchForLogsQuery($subQuery));
                }
            })
            ->when($resolved, function ($q) use ($resolved): void {
                if ($resolved === 'resolved') {
                    $q->where('resolved', true);
                } elseif ($resolved === 'unresolved') {
                    $q->where('resolved', false);
                }
            })
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Devuelve el número de logs por severidad y estado resolved/unresolved.
     * No construye la card "Todos": solo devuelve buckets por severidad.
     * Si $includeArchived es false, excluye logs con equivalente en archived_logs.
     *
     * @return array<string,array{resolved:int,unresolved:int,total:int}>
     */
    public function severityResolvedCounts(bool $includeArchived = false): array
    {
        $severities = Severity::values();

        $rows = Log::query()
            ->when(!$includeArchived, fn ($q) => $q->whereNotExists(fn ($subQuery) => $this->applyArchivedMatchForLogsQuery($subQuery)))
            ->selectRaw('severity, resolved, count(*) as count')
            ->whereIn('severity', $severities)
            ->groupBy('severity', 'resolved')
            ->get();

        $result = [];
        foreach ($severities as $severity) {
            $result[$severity] = [
                'resolved' => 0,
                'unresolved' => 0,
                'total' => 0,
            ];
        }

        foreach ($rows as $row) {
            $severity = (string) $row->severity;
            $count = (int) $row->count;
            $bucket = (bool) $row->resolved ? 'resolved' : 'unresolved';

            if (!isset($result[$severity])) {
                continue;
            }

            $result[$severity][$bucket] += $count;
            $result[$severity]['total'] += $count;
        }

        return $result;
    }

    /**
     * Devuelve el número total de logs para el scope solicitado.
     * Si $includeArchived es false, excluye logs con equivalente en archived_logs.
     */
    public function logsCount(bool $includeArchived = false): int
    {
        return Log::query()
            ->when(!$includeArchived, fn ($q) => $q->whereNotExists(fn ($subQuery) => $this->applyArchivedMatchForLogsQuery($subQuery)))
            ->count();
    }

    /**
     * Devuelve el id de ArchivedLog equivalente al log o null si no está archivado.
     */
    public function archivedLogIdFor(int $logId): ?int
    {
        $log = Log::query()
            ->whereKey($logId)
            ->first();

        if ($log === null) {
            return null;
        }

        $archivedQuery = ArchivedLog::query();
        $this->applyArchivedMatchForConcreteLog($archivedQuery, $log);
        $archivedId = $archivedQuery->value('id');

        return $archivedId !== null ? (int) $archivedId : null;
    }

    /**
     * Aplica a una subquery la condición de equivalencia
     * archived_logs <-> logs (fila externa).
     *
     * Se usa para whereExists/whereNotExists y para calcular is_archived.
     */
    private function applyArchivedMatchForLogsQuery(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query
            ->from('archived_logs')
            ->whereColumn('archived_logs.application_id', 'logs.application_id')
            ->whereRaw('archived_logs.error_code_id IS NOT DISTINCT FROM logs.error_code_id')
            ->whereColumn('archived_logs.severity', 'logs.severity')
            ->whereColumn('archived_logs.message', 'logs.message')
            ->whereColumn('archived_logs.original_created_at', 'logs.created_at');
    }

    /**
     * Aplica la misma condición de equivalencia para un Log concreto.
     * Se usa para obtener el id del ArchivedLog equivalente.
     */
    private function applyArchivedMatchForConcreteLog(Builder $query, Log $log): Builder
    {
        return $query
            ->where('application_id', $log->application_id)
            ->whereRaw('error_code_id IS NOT DISTINCT FROM ?', [$log->error_code_id])
            ->where('severity', $log->severity)
            ->where('message', $log->message)
            ->where('original_created_at', $log->created_at);
    }
}
